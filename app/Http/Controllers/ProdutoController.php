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
    private const LIMITE_DESTAQUES = 10;

    public function index(Request $request)
    {
        $query = Produto::with('categoria');

        $idsEncontrados = collect();

        if ($request->has('search') && $request->search != '') {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });

            $idsEncontrados = (clone $query)->pluck('id');

            $query->orderByRaw(
                'CASE WHEN nome = ? THEN 0 WHEN nome LIKE ? THEN 1 WHEN nome LIKE ? THEN 2 ELSE 3 END',
                [$search, "{$search}%", "%{$search}%"]
            );
        }

        $produtos = $query->orderByDesc('destaque')->orderBy('ordem_destaque')->orderBy('id')->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 
        [$relacionados, $tipoSugestao] = $this->sugestoes($request, $produtos, $idsEncontrados);

        return view('produtos.index', compact('produtos', 'categorias', 'relacionados', 'tipoSugestao'));
    }

    public function porCategoria(Request $request, $slug)
    {
        $categoriaAtiva = Categoria::where('slug', $slug)->firstOrFail();
        
        $query = Produto::where('categoria_id', $categoriaAtiva->id);

        $idsEncontrados = collect();

        if ($request->has('search') && $request->search != '') {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });

            $idsEncontrados = (clone $query)->pluck('id');

            $query->orderByRaw(
                'CASE WHEN nome = ? THEN 0 WHEN nome LIKE ? THEN 1 WHEN nome LIKE ? THEN 2 ELSE 3 END',
                [$search, "{$search}%", "%{$search}%"]
            );
        }
        
        $produtos = $query->orderByDesc('destaque')->orderBy('ordem_destaque')->orderBy('id')->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 
        [$relacionados, $tipoSugestao] = $this->sugestoes($request, $produtos, $idsEncontrados);

        return view('produtos.index', compact('produtos', 'categorias', 'categoriaAtiva', 'relacionados', 'tipoSugestao'));
    }

    private function sugestoes(Request $request, $produtos, Collection $idsEncontrados): array
    {
        $search = trim((string) $request->input('search'));

        if ($search === '') {
            return [collect(), null];
        }

        if ($produtos->total() > 0) {
            return [$this->maisDaPesquisa($search, $idsEncontrados), 'mais'];
        }

        $relacionados = $this->produtosRelacionados($search);
        if ($relacionados->isNotEmpty()) {
            return [$relacionados, 'relacionados'];
        }

        return [$this->maisVendidos(), 'populares'];
    }

    private function maisDaPesquisa(string $search, Collection $idsEncontrados, int $limite = 10): Collection
    {
        $lista = $this->produtosRelacionados($search, 20)
            ->reject(fn ($p) => $idsEncontrados->contains($p->id));

        $categorias = Produto::whereIn('id', $idsEncontrados)->pluck('categoria_id')->filter()->unique();

        if ($categorias->isNotEmpty() && $lista->count() < $limite) {
            $mesmaCategoria = Produto::with('categoria')
                ->whereIn('categoria_id', $categorias)
                ->whereNotIn('id', $idsEncontrados->merge($lista->pluck('id')))
                ->orderByDesc('destaque')
                ->orderBy('ordem_destaque')
                ->orderBy('id')
                ->limit($limite)
                ->get();

            $lista = $lista->concat($mesmaCategoria);
        }

        if ($lista->count() < $limite) {
            $usados = $idsEncontrados->merge($lista->pluck('id'));
            $lista  = $lista->concat(
                $this->maisVendidos($limite * 2)->reject(fn ($p) => $usados->contains($p->id))
            );
        }

        return $lista->unique('id')->take($limite)->values();
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
            if (Produto::where('destaque', true)->count() >= self::LIMITE_DESTAQUES) {
                $erro = 'Limite de ' . self::LIMITE_DESTAQUES . ' destaques atingido. Remova um antes de adicionar outro.';

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $erro], 422);
                }

                return redirect()->back()->with('error', $erro);
            }

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

        $limite = self::LIMITE_DESTAQUES;

        return view('admin.destaques', compact('destaques', 'outros', 'limite'));
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
        $produto = Produto::with(['categoria', 'imagens'])->findOrFail($id);
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
            'imagens' => 'required|array|min:1|max:5',
            'imagens.*' => 'image|max:2048',
        ], $this->mensagensFotos());

        $fotos = $request->file('imagens');
        unset($data['imagens']);

        $data['imagem'] = $fotos[0]->store('products', 'public');
        $produto = Produto::create($data);

        foreach (array_slice($fotos, 1) as $indice => $foto) {
            $produto->imagens()->create([
                'caminho' => $foto->store('products', 'public'),
                'ordem'   => $indice + 1,
            ]);
        }

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit($id) 
    {
        $produto = Produto::with('imagens')->findOrFail($id);
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
            'imagens' => 'nullable|array|max:4',
            'imagens.*' => 'image|max:2048',
            'remover_imagens' => 'nullable|array',
            'remover_imagens.*' => 'integer',
        ], $this->mensagensFotos());

        $remover   = $produto->imagens()->whereIn('id', $request->input('remover_imagens', []))->get();
        $novas     = $request->file('imagens', []);
        $restantes = $produto->imagens()->count() - $remover->count();

        if ($restantes + count($novas) > 4) {
            return redirect()->back()->withInput()->withErrors([
                'imagens' => 'O produto pode ter no máximo 5 fotos (a capa + 4 extras).',
            ]);
        }

        foreach ($remover as $foto) {
            Storage::disk('public')->delete($foto->caminho);
            $foto->delete();
        }

        $ordem = (int) $produto->imagens()->max('ordem');
        foreach ($novas as $foto) {
            $produto->imagens()->create([
                'caminho' => $foto->store('products', 'public'),
                'ordem'   => ++$ordem,
            ]);
        }

        unset($data['imagens'], $data['remover_imagens']);

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
        $produto = Produto::with('imagens')->findOrFail($id);
        
        if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
            Storage::disk('public')->delete($produto->imagem);
        }

        foreach ($produto->imagens as $foto) {
            Storage::disk('public')->delete($foto->caminho);
        }

        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Produto removido!');
    }

    private function mensagensFotos(): array
    {
        return [
            'imagens.required' => 'Escolha pelo menos uma foto.',
            'imagens.max'      => 'Você pode enviar no máximo 5 fotos.',
            'imagens.*.image'  => 'Todos os arquivos precisam ser imagens.',
            'imagens.*.max'    => 'Cada foto pode ter no máximo 2 MB.',
        ];
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