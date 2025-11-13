<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParametroGlobal; // Importa o Model criado anteriormente
use App\Models\ParametroTaxaAliquota;
use App\Models\ProdutoCategoria;
use App\Models\Organizacao;
use App\Models\Produto;
use Illuminate\Validation\Rule;

class ParametroGlobalController extends Controller
{
    /**
     * Aplica o middleware de permissão.
     */
    public function __construct()
    {
        // Apenas quem tem a permissão pode acessar qualquer método deste controller
        $this->middleware('permission:view parametros globais', ['only' => ['index']]);
        $this->middleware('permission:edit parametros globais', ['only' => ['update', 'storeTaxa']]);
        $this->middleware('permission:create parametros globais', ['only' => ['storeTaxa']]);
        $this->middleware('permission:delete parametros globais', ['only' => ['destroyTaxa']]);


    }

    /**
     * Exibe a página de edição dos parâmetros globais.
     * Busca o primeiro registro ou cria um com valores padrão se não existir.
     */
    public function index()
    {
        $parametros = ParametroGlobal::firstOrCreate(
            [],
            [
                'descontar_ir_fatura' => false,
                'dias_vencimento_publico' => 30,
                'dias_vencimento_privado' => 15
            ]
        );

        // ALTERADO: Busca por Categoria e renomeia relacionamento no 'with'
        $taxas = ParametroTaxaAliquota::with(['organizacao', 'produtoCategoria'])->get(); // <- Relacionamento ajustado
        $organizacoes = Organizacao::orderBy('nome')->get();
        $categorias = ProdutoCategoria::orderBy('nome')->get(); // ALTERADO: Busca categorias

        // ALTERADO: Passa $categorias para a view em vez de $produtos
        return view('admin.parametros_globais.index', compact('parametros', 'taxas', 'organizacoes', 'categorias'));
    }

    /**
     * Atualiza os parâmetros globais no banco de dados.
     */
    public function update(Request $request)
    {
        $request->validate([
            'descontar_ir_fatura' => 'nullable|string', // Checkbox envia 'on' ou nada
            'dias_vencimento_publico' => 'required|integer|min:0',
            'dias_vencimento_privado' => 'required|integer|min:0',
        ]);

        // Busca o primeiro registro (garantido existir pelo método index)
        $parametros = ParametroGlobal::first();

        if ($parametros) {
            $parametros->update([
                'descontar_ir_fatura' => $request->has('descontar_ir_fatura'), // Converte 'on' para true, ausência para false
                'dias_vencimento_publico' => $request->input('dias_vencimento_publico'),
                'dias_vencimento_privado' => $request->input('dias_vencimento_privado'),
            ]);
        }
        // Se, por algum motivo, não encontrar (improvável), pode adicionar um else para criar.

        // Limpa o cache de configuração (importante se você usar config() para ler parâmetros)
        \Illuminate\Support\Facades\Artisan::call('config:clear'); // Descomente se necessário

        return redirect()->route('admin.parametros.globais.index')
                         ->with('success', 'Parâmetros globais atualizados com sucesso!');
    }

    public function updateBanco(Request $request)
    {
        $validated = $request->validate([
            'banco' => 'nullable|string|max:255',
            'agencia' => 'nullable|string|max:255',
            'conta' => 'nullable|string|max:255',
            'chave_pix' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'razao_social' => 'nullable|string|max:255',
        ]);

        $parametros = \App\Models\ParametroGlobal::first();

        if (!$parametros) {
            return redirect()->route('admin.parametros.globais.index')
                         ->with('success', 'Registro de parâmetros globais não encontrado.');
        }

        $parametros->update($validated);

        return redirect()->route('admin.parametros.globais.index')
                         ->with('success', 'Dados bancários atualizados com sucesso!');
    }

    /**
     * Reseta os parâmetros globais para os valores padrão.
     */
    public function resetDefaults()
    {
        $parametros = ParametroGlobal::first();

        if ($parametros) {
            $parametros->update([
                'descontar_ir_fatura' => false,
                'dias_vencimento_publico' => 30,
                'dias_vencimento_privado' => 15,
            ]);
        } else {
            // Se por algum motivo não existir, cria com os padrões
             ParametroGlobal::create([
                'descontar_ir_fatura' => false,
                'dias_vencimento_publico' => 30,
                'dias_vencimento_privado' => 15
            ]);
        }

        return redirect()->route('admin.parametros.globais.index')
                         ->with('success', 'Parâmetros globais resetados para os valores padrão!');
    }
    /**
     * Salva ou atualiza as taxas/alíquotas.
     */

    public function storeTaxa(Request $request)
    {
        // <<< ADICIONE ESTA LINHA PARA VER OS DADOS RECEBIDOS >>>
        //dd($request->all());

        $request->validate([
            'organizacao_id' => [
                'required',
                'exists:organizacao,id',
                 // ALTERADO: Validação unique composta agora usa produto_categoria_id
                Rule::unique('parametro_taxa_aliquota')->where(function ($query) use ($request) {
                    return $query->where('produto_categoria_id', $request->produto_categoria_id); // ALTERADO
                })->ignore($request->input('taxa_id'))
            ],
            // ALTERADO: Validação para categoria
            'produto_categoria_id' => 'required|exists:produto_categoria,id',
            'taxa_aliquota' => 'required|numeric|min:0|max:100',
            'taxa_id' => 'nullable|exists:parametro_taxa_aliquota,id'
        ], [
             // ALTERADO: Mensagem de erro
            'organizacao_id.unique' => 'Já existe uma taxa definida para esta combinação de Organização e Categoria.',
        ]);

        $taxaDecimal = $request->taxa_aliquota / 100;

        ParametroTaxaAliquota::updateOrCreate(
            ['id' => $request->input('taxa_id')],
            [
                'organizacao_id' => $request->organizacao_id,
                 // ALTERADO: Salva o ID da categoria
                'produto_categoria_id' => $request->produto_categoria_id,
                'taxa_aliquota' => $taxaDecimal,
            ]
        );

        $message = $request->input('taxa_id') ? 'Taxa/Alíquota atualizada!' : 'Nova Taxa/Alíquota adicionada!';

        return redirect()->route('admin.parametros.globais.index')->with('success', $message);
    }

     /**
     * Remove uma taxa/alíquota específica.
     */
    public function destroyTaxa(ParametroTaxaAliquota $taxa)
    {
        $taxa->delete();
        return redirect()->route('admin.parametros.globais.index')
                         ->with('success', 'Taxa/Alíquota removida com sucesso!');
    }
}

