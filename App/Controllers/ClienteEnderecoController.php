<?php

namespace App\Controllers;

use App\Models\Cliente;
use App\Models\ClienteEndereco;
use App\Rules\Logged;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class ClienteEnderecoController extends Controller
{
    protected $post;
    protected $get;
    protected $layout;
    protected $idEmpresa;
    protected $idUsuario;
    protected $idPerfilUsuarioLogado;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'default';

        $this->post = new Post();
        $this->get = new Get();
        $this->idEmpresa = (int) Session::get('idEmpresa');
        $this->idUsuario = (int) Session::get('idUsuario');
        $this->idPerfilUsuarioLogado = (int) Session::get('idPerfil');

        $logged = new Logged();
        $logged->isValid();
    }

    public function save()
    {
        if ($this->post->hasPost()) {
            $clienteEndereco = new ClienteEndereco();
            $dados = (array)$this->post->only([
                'id_cliente', 'cep', 'endereco', 'bairro',
                'cidade', 'estado', 'numero', 'complemento'
            ]);
            $dados['id_empresa'] = $this->idEmpresa;
            $dados['numero'] = isset($dados['numero']) ? preg_replace('/\D+/', '', (string) $dados['numero']) : null;

            try {
                if (empty($dados['numero'])) {
                    throw new \InvalidArgumentException('O número do endereço deve conter apenas dígitos.');
                }

                $clienteEndereco->save($dados);
                if ($this->isAjaxRequest()) {
                    echo json_encode(['status' => true, 'id' => $clienteEndereco->lastId()]);
                    return;
                }
                return $this->get->redirectTo("cliente");

            } catch (\Exception $e) {
                if ($this->isAjaxRequest()) {
                    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
                    return;
                }
                echo $e->getMessage();
            }
        }
    }

    public function update()
    {
        $clienteEndereco = new ClienteEndereco();
        $dadosClienteEndereco = $clienteEndereco->find($this->post->data()->id);
        $dados = (array)$this->post->only([
            'cep', 'endereco', 'bairro', 'cidade',
            'estado', 'numero', 'complemento'
        ]);
        $dados['numero'] = isset($dados['numero']) ? preg_replace('/\D+/', '', (string) $dados['numero']) : null;

        try {
            if (empty($dados['numero'])) {
                throw new \InvalidArgumentException('O número do endereço deve conter apenas dígitos.');
            }

            $clienteEndereco->update($dados, $dadosClienteEndereco->id);
            if ($this->isAjaxRequest()) {
                echo json_encode(['status' => true, 'id' => $dadosClienteEndereco->id]);
                return;
            }
            return $this->get->redirectTo("cliente");

        } catch (\Exception $e) {
            if ($this->isAjaxRequest()) {
                echo json_encode(['status' => false, 'message' => $e->getMessage()]);
                return;
            }
            echo $e->getMessage();
        }
    }

    public function buscarEnderecoViaCep($cep)
    {
        $cep = out64($cep);
        $cep = str_replace('.', '', $cep);
        $cep = str_replace('-', '', $cep);

        $resposta = fileGet("https://viacep.com.br/ws/{$cep}/json/");

        if (!isset($resposta['erro'])) {
            echo json_encode(['status' => true, 'conteudo' => $resposta]);
        } else {
            echo json_encode(['status' => false, 'mensagem' => 'Cep não encontrado!']);
        }
    }

    public function modalFormulario($idCliente, $idEnderecoCliente = false)
    {
        $clienteEndereco = false;

        if ($idEnderecoCliente) {
            $clienteEndereco = new ClienteEndereco();
            $clienteEndereco = $clienteEndereco->find($idEnderecoCliente);
        }

        $this->view('clienteEndereco/formulario', null,
            compact(
                'clienteEndereco',
                'idCliente'
            ));
    }

    public function modalVisualizarEnderecos($idCliente)
    {
        $cliente = new Cliente();
        $cliente = $cliente->find(out64($idCliente));

        $clienteEndereco = new ClienteEndereco();
        $clienteEnderecos = $clienteEndereco->enderecos($cliente->id);

        $this->view('clienteEndereco/lista_de_enderecos', null,
            compact(
                'clienteEnderecos',
                'cliente'
            ));
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
