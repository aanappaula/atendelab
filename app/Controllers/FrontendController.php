<?php

class FrontendController
{
    public function pessoas(): void
    {
        $tituloPagina = 'Pessoas atendidas';
        require __DIR__ . '/../Views/pessoas/index.php';
    }

    public function tipos(): void
    {
        $tituloPagina = 'Tipos de atendimento';
        require __DIR__ . '/../Views/tipos-atendimentos/index.php';
    }

    public function atendimentos(): void
    {
        $tituloPagina = 'Atendimentos';
        require __DIR__ . '/../Views/atendimentos/index.php';
    }
}
