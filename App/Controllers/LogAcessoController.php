<?php

namespace App\Controllers;

use App\Config\ConfigPerfil;
use App\Models\LogAcesso;
use App\Rules\Logged;
use App\Rules\PerfilAcessoRule;
use System\Controller\Controller;
use System\Session\Session;

class LogAcessoController extends Controller
{
    protected $layout;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'default';

        $this->idEmpresa = (int) Session::get('idEmpresa');
        $this->idUsuario = (int) Session::get('idUsuario');

        $logged = new Logged();
        $logged->isValid();
        (new PerfilAcessoRule())->exigePerfis(
            [ConfigPerfil::superAdmin()],
            'home',
            'Logs de acesso estão disponíveis apenas para o SuperAdmin.'
        );
    }

    public function index()
    {
        $model = new LogAcesso();
        $logs = $model->logs($this->idEmpresa);

        foreach ($logs as &$log) {
            $log->data = date('d/m/Y', strtotime($log->created_at));
            $log->hora = date('H:i', strtotime($log->created_at)) . 'h';
        }

        $this->view('logs/index', $this->layout, compact("logs"));
    }

}

