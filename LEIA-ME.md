# Histórico de compras (comprador) e de vendas (admin)

## Arquivos NOVOS
| Arquivo | Onde criar |
|---|---|
| `2026_10_25_000001_create_pedidos_table.php` | `database/migrations/` |
| `Pedido.php` | `app/Models/` |
| `PedidoItem.php` | `app/Models/` |
| `PedidoController.php` | `app/Http/Controllers/` |
| `historico-compras.blade.php` | `resources/views/profile/partials/` |
| `pedidos.blade.php` | `resources/views/admin/` (crie a pasta `admin`) |

## Arquivos para SUBSTITUIR
| Arquivo | Onde está |
|---|---|
| `CheckoutController.php` | `app/Http/Controllers/` |
| `ProfileController.php` | `app/Http/Controllers/` |
| `User.php` | `app/Models/` |
| `edit.blade.php` | `resources/views/profile/` |
| `navigation.blade.php` | `resources/views/layouts/` |
| `web.php` | `routes/` |

## Depois de copiar
```bash
php artisan migrate
php artisan route:clear
php artisan view:clear
```

Deixe o `npm run dev` rodando (ou rode `npm run build`) para o Tailwind gerar as classes novas.

## Onde aparece
- **Comprador:** menu do usuário → **Meu Perfil** (ou **Minhas Compras**). Seção "Histórico de compras".
- **Admin:** link **Vendas** na barra de cima, ou menu do usuário → **Histórico de Vendas**.
  Mostra os pedidos de todos os usuários, totais, faturamento e filtros por cliente, status e método.

Os pedidos feitos **antes** dessa mudança não aparecem, porque antes eles só ficavam na sessão.
