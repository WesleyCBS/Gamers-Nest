<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::with('categoria');

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });
        }

        $produtos = $query->orderByDesc('destaque')->orderBy('ordem_destaque')->orderBy('id')->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 
        [$relacionados, $tipoSugestao] = $this->sugestoes($request, $produtos);

        return view('produtos.index', compact('produtos', 'categorias', 'relacionados', 'tipoSugestao'));
    }

    public function porCategoria(Request $request, $slug)
    {
        $categoriaAtiva = Categoria::where('slug', $slug)->firstOrFail();
        
        $query = Produto::where('categoria_id', $categoriaAtiva->id);

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });
        }
        
        $produtos = $query->orderByDesc('destaque')->orderBy('ordem_destaque')->orderBy('id')->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 
        [$relacionados, $tipoSugestao] = $this->sugestoes($request, $produtos);

        return view('produtos.index', compact('produtos', 'categorias', 'categoriaAtiva', 'relacionados', 'tipoSugestao'));
    }

    private function sugestoes(Request $request, $produtos): array
    {
        $search = trim((string) $request->input('search'));

        if ($search === '' || $produtos->total() > 0) {
            return [collect(), null];
        }

        $relacionados = $this->produtosRelacionados($search);
        if ($relacionados->isNotEmpty()) {
            return [$relacionados, 'relacionados'];
        }

        return [$this->maisVendidos(), 'populares'];
    }

    private function normalizar(?string $texto): string
    {
        return Str::of((string) $texto)->ascii()->lower()->value();
    }

    private function produtosRelacionados(string $search, int $limite = 8): Collection
    {
        $ignorar = ['de', 'da', 'do', 'das', 'dos', 'para', 'com', 'sem', 'por', 'uma', 'um', 'the', 'and', 'jogo', 'jogos'];

        $termos = collect(preg_split('/[^a-z0-9]+/', $this->normalizar($search)))
            ->filter(fn ($t) => strlen($t) >= 2 && !in_array($t, $ignorar))
            ->unique()
            ->values();

        if ($termos->isEmpty()) {
            return collect();
        }

        return Produto::with('categoria')->get()
            ->map(function ($produto) use ($termos) {
                $nome      = $this->normalizar($produto->nome);
                $descricao = $this->normalizar($produto->descrição);
                $categoria = $this->normalizar($produto->categoria->nome ?? '');
                $palavras  = array_filter(preg_split('/[^a-z0-9]+/', "$nome $categoria"), fn ($p) => strlen($p) >= 2);

                $pontos = 0;
                foreach ($termos as $termo) {
                    if (str_contains($nome, $termo)) {
                        $pontos += 10;
                    } elseif (str_contains($categoria, $termo)) {
                        $pontos += 6;
                    } elseif (str_contains($descricao, $termo)) {
                        $pontos += 4;
                    } elseif (strlen($termo) >= 4) {
                        $tolerancia = strlen($termo) <= 7 ? 1 : 2;
                        foreach ($palavras as $palavra) {
                            if (levenshtein($termo, $palavra) <= $tolerancia) {
                                $pontos += 3;
                                break;
                            }
                        }
                    }
                }

                $produto->pontos_busca = $pontos;
                return $produto;
            })
            ->filter(fn ($p) => $p->pontos_busca > 0)
            ->sortByDesc('pontos_busca')
            ->take($limite)
            ->values();
    }

    private function maisVendidos(int $limite = 8): Collection
    {
        $vendidos = collect();

        if (Schema::hasTable('pedido_itens')) {
            $ids = DB::table('pedido_itens')
                ->whereNotNull('produto_id')
                ->select('produto_id', DB::raw('SUM(quantidade) as total'))
                ->groupBy('produto_id')
                ->orderByDesc('total')
                ->limit($limite)
                ->pluck('produto_id');

            $vendidos = Produto::with('categoria')->whereIn('id', $ids)->get()
                ->sortBy(fn ($p) => $ids->search($p->id))
                ->values();
        }

        if ($vendidos->count() < $limite) {
            $completar = Produto::with('categoria')
                ->whereNotIn('id', $vendidos->pluck('id'))
                ->latest()
                ->limit($limite - $vendidos->count())
                ->get();

            $vendidos = $vendidos->concat($completar)->values();
        }

        return $vendidos;
    }

    public function alternarDestaque(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);

        if ($produto->destaque) {
            $produto->update(['destaque' => false, 'ordem_destaque' => null]);
            $this->reordenarDestaques();
            $mensagem = 'Produto removido dos destaques.';
        } else {
            $proxima = (int) Produto::where('destaque', true)->max('ordem_destaque') + 1;
            $produto->update(['destaque' => true, 'ordem_destaque' => $proxima]);
            $mensagem = 'Produto adicionado aos destaques!';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'destaque' => $produto->destaque,
                'message'  => $mensagem,
            ]);
        }

        return redirect()->back()->with('success', $mensagem);
    }

    public function destaques()
    {
        $destaques = Produto::with('categoria')->where('destaque', true)->orderBy('ordem_destaque')->get();
        $outros    = Produto::with('categoria')->where('destaque', false)->orderBy('nome')->get();

        return view('admin.destaques', compact('destaques', 'outros'));
    }

    public function moverDestaque($id, $direcao)
    {
        $this->reordenarDestaques();

        $produto = Produto::where('destaque', true)->findOrFail($id);
        $subir   = $direcao === 'cima';

        $vizinho = Produto::where('destaque', true)
            ->where('ordem_destaque', $subir ? '<' : '>', $produto->ordem_destaque)
            ->orderBy('ordem_destaque', $subir ? 'desc' : 'asc')
            ->first();

        if ($vizinho) {
            $posicao = $produto->ordem_destaque;
            $produto->update(['ordem_destaque' => $vizinho->ordem_destaque]);
            $vizinho->update(['ordem_destaque' => $posicao]);
        }

        return redirect()->route('admin.destaques');
    }

    private function reordenarDestaques(): void
    {
        Produto::where('destaque', true)
            ->orderBy('ordem_destaque')
            ->orderBy('id')
            ->get()
            ->each(fn ($p, $i) => $p->update(['ordem_destaque' => $i + 1]));
    }

    public function show($id) 
    {
        $produto = Produto::with('categoria')->findOrFail($id);
        return view('produtos.show', compact('produto'));
    }

    public function create()
    {
        $categorias = Categoria::all(); 
        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descrição' => 'nullable|string',
            'preço' => 'required|numeric',
            'quantidade' => 'required|integer',
            'categoria_id' => 'required|exists:categorias,id',
            'imagem' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('products', 'public');
            $data['imagem'] = $path; 
        }

        Produto::create($data);
        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit($id) 
    {
        $produto = Produto::findOrFail($id);
        $categorias = Categoria::all();
        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, $id) 
    {
        $produto = Produto::findOrFail($id);
        
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descrição' => 'nullable|string',
            'preço' => 'required|numeric',
            'quantidade' => 'required|integer',
            'categoria_id' => 'required|exists:categorias,id',
            'imagem' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $path = $request->file('imagem')->store('products', 'public');
            $data['imagem'] = $path;
        }

        $produto->update($data);
        return redirect()->route('produtos.index')->with('success', 'Produto atualizado!');
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        
        if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
            Storage::disk('public')->delete($produto->imagem);
        }

        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Produto removido!');
    }

    public function favorito(Request $request, $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user(); 

        try {
            $user->favoritos()->toggle($id); 
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao favoritar.');
        }
    }

    public function favoritos()
    {
        if (!Auth::check()) return redirect()->route('login');

        $produtosFavoritos = Auth::user()->favoritos;
        $categorias = Categoria::all(); 
        return view('produtos.favoritos', compact('produtosFavoritos', 'categorias'));
    }

    // ADICIONADO/CORRIGIDO: Aponta para a pasta correta 'produtos.endereco'
    public function endereco($id = null)
    {
        $produto = $id ? Produto::findOrFail($id) : null;
        return view('produtos.endereco', compact('produto'));
    }
}