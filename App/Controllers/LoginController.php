<?php

namespace App\Controllers;

use App\Config\ConfigPerfil;
use App\Models\Caixa;
use App\Models\CaixaSessao;
use App\Models\LogAcesso;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Rules\Logged;
use App\Services\LoginRemeber\LoginRemeber;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class LoginController extends Controller
{
    protected Post   $post;
    protected Get    $get;
    protected string $layout = 'login';

    public function __construct()
    {
        parent::__construct();
        $this->post = new Post();
        $this->get  = new Get();

        $logged = new Logged();
        $logged->logged();
    }

    public function index(): void
    {
        $this->view('login/index', $this->layout);
    }

    public function caixasPorEmail(): void
    {
        $email = trim($this->post->data()->email ?? '');
        if (!$email) {
            echo json_encode(['status' => false, 'caixas' => []]);
            return;
        }

        $usuario = (new Usuario())->findBy('email', $email);
        if (!$usuario || (int) $usuario->id_perfil !== ConfigPerfil::vendedor()) {
            echo json_encode(['status' => true, 'isVendedor' => false, 'caixas' => []]);
            return;
        }

        $caixas = (new Caixa())->livres((int) $usuario->id_empresa);
        echo json_encode([
            'status' => true,
            'isVendedor' => true,
            'caixas' => array_map(static function ($caixa) {
                return [
                    'id' => $caixa->id,
                    'nome' => $caixa->nome,
                ];
            }, $caixas)
        ]);
    }

    public function logar(): void
    {
        $email    = trim($this->post->data()->email ?? '');
        $password = $this->post->data()->password ?? '';
        $remember = ($this->post->data()->remember ?? '') === 'on';

        $usuario      = new Usuario();
        $dadosUsuario = $usuario->findBy('email', $email);

        if ($dadosUsuario && $usuario->userExist(['email' => $email, 'password' => $password])) {
            // Gravar log de acesso
            $log = new LogAcesso();
            $log->save([
                'id_usuario' => $dadosUsuario->id,
                'id_empresa' => $dadosUsuario->id_empresa,
            ]);

            $perfil = (new Perfil())->find($dadosUsuario->id_perfil);

            // Dados na sessão
            Session::set('logged',        true);
            Session::set('idUsuario',     $dadosUsuario->id);
            Session::set('idPerfil',      $dadosUsuario->id_perfil);
            Session::set('legendaPerfil', $perfil ? $perfil->descricao : 'Usuário');
            Session::set('idEmpresa',     $dadosUsuario->id_empresa);
            Session::set('nomeUsuario',   $dadosUsuario->nome);
            Session::set('idSexoUsuario', $dadosUsuario->id_sexo);
            Session::set('emailUsuario',  $dadosUsuario->email);
            Session::set('imagem',        $dadosUsuario->imagem);
            Session::delete('idCaixaSessao');
            Session::delete('idCaixaAtual');
            Session::delete('nomeCaixaAtual');

            if ($remember) {
                $loginRemember = new LoginRemeber($dadosUsuario);
                $loginRemember->execute();
            }

            $this->get->redirectTo('home');
        }

        Session::flash('error', 'E-mail ou senha inválidos!');
        $this->get->redirectTo('login');
    }

    public function logout(): void
    {
        $loginRemember = new LoginRemeber();
        $hash = $loginRemember->getRememberCookie();

        if (!is_null($hash)) {
            $usuario = new Usuario();
            $user    = $usuario->findBy('remember_token', $hash);
            if ($user) {
                $usuario->update(['remember_token' => null, 'remember_expire_date' => null], $user->id);
            }
            $loginRemember->deleteRememberCookie();
        }

        Session::logout();
        $this->get->redirectTo('login');
    }
}
