# Padrões de Código

## Controller — thin, sempre

O controller só recebe a requisição, delega ao Service e retorna a resposta.
Nenhuma regra de negócio aqui.

```php
// ✅ Correto
class ContaPagarController extends Controller
{
    public function store(StoreContaPagarRequest $request, ContaPagarService $service)
    {
        $conta = $service->criar($request->validated());
        return redirect()->route('financeiro.contas-pagar.show', $conta);
    }

    public function index(ContaPagarService $service)
    {
        $contas = $service->listar(request()->all());
        return Inertia::render('Financeiro/ContaPagar/Index', compact('contas'));
    }
}

// ❌ Errado — regra de negócio no controller
public function store(Request $request)
{
    if ($request->valor > 5000 && !auth()->user()->can('aprovar-alto-valor')) {
        abort(403);
    }
    ContaPagar::create($request->all()); // nunca criar direto no controller
}
```

---

## Form Request — validação na entrada

```php
class StoreContaPagarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('financeiro.contas-pagar.criar');
    }

    public function rules(): array
    {
        return [
            'fornecedor_id' => ['required', 'exists:fornecedores,id'],
            'valor'         => ['required', 'numeric', 'min:0.01'],
            'vencimento'    => ['required', 'date', 'after:today'],
            'descricao'     => ['required', 'string', 'max:255'],
        ];
    }
}
```

---

## Service — onde mora a regra de negócio

```php
class ContaPagarService
{
    public function __construct(
        private ContaPagarRepository $contas,
        private ParametroService $parametros,
    ) {}

    public function criar(array $dados): ContaPagar
    {
        // 1. regras de negócio
        $limiteAprovacao = $this->parametros->get('financeiro.contas-pagar.limite_aprovacao');
        if ($dados['valor'] > $limiteAprovacao) {
            throw new AprovacaoNecessariaException();
        }

        // 2. persistência via repository
        $conta = $this->contas->criar($dados);

        // 3. side effects assíncronos
        NotificarAprovadorJob::dispatch($conta)->onQueue('notifications');

        return $conta;
    }
}
```

---

## Repository — isolar acesso a dados

```php
// Sempre definir a interface primeiro
interface ContaPagarRepositoryInterface
{
    public function criar(array $dados): ContaPagar;
    public function buscarPorId(int $id): ContaPagar;
    public function listar(array $filtros): LengthAwarePaginator;
}

class ContaPagarRepository implements ContaPagarRepositoryInterface
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        return ContaPagar::query()
            ->with(['fornecedor'])
            ->when($filtros['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filtros['vencimento_de'] ?? null, fn($q, $v) => $q->whereDate('vencimento', '>=', $v))
            ->orderBy('vencimento')
            ->paginate(25);
    }
}
```

Registrar no ServiceProvider do módulo:
```php
$this->app->bind(ContaPagarRepositoryInterface::class, ContaPagarRepository::class);
```

---

## Jobs — operações assíncronas

```php
class NotificarAprovadorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private ContaPagar $conta) {}

    public function handle(NotificacaoService $service): void
    {
        $service->notificarAprovacao($this->conta);
    }
}

// Disparar:
NotificarAprovadorJob::dispatch($conta)->onQueue('notifications');
```

Usar Jobs obrigatoriamente para:
- Envio de email
- Geração de PDF
- Relatórios e exportações
- Sincronizações externas
- Notificações WhatsApp (futuro)

---

## Model — sem lógica, com auditoria

```php
class ContaPagar extends Model
{
    use LogsActivity;

    protected $fillable = [
        'fornecedor_id', 'valor', 'vencimento', 'status', 'descricao',
    ];

    protected $casts = [
        'valor'      => 'decimal:2',
        'vencimento' => 'date',
    ];

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    // Relacionamentos
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
```

---

## Testes — Pest PHP

### Unit (Service isolado)
```php
it('lança exceção quando valor excede limite de aprovação', function () {
    $parametros = mock(ParametroService::class)
        ->expect(get: fn($chave) => 5000.00);

    $service = new ContaPagarService(
        contas: mock(ContaPagarRepositoryInterface::class),
        parametros: $parametros,
    );

    expect(fn() => $service->criar(['valor' => 6000.00]))
        ->toThrow(AprovacaoNecessariaException::class);
});
```

### Feature (HTTP completo)
```php
it('financeiro pode criar conta a pagar dentro do limite', function () {
    $user = User::factory()->withRole('financeiro')->create();

    actingAs($user)
        ->post(route('financeiro.contas-pagar.store'), [
            'fornecedor_id' => Fornecedor::factory()->create()->id,
            'valor'         => 1000.00,
            'vencimento'    => now()->addDays(30)->toDateString(),
            'descricao'     => 'Material de escritório',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});
```

Rodar os testes:
```bash
docker compose exec app php artisan test
docker compose exec app php artisan test --filter=ContaPagar
```
