<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail 
{
    public function notificarTerminoLeilao(Leilao $leilao)
    {
        $sucesso = mail(
            'weslleymardev15@gmail.com',
            'Leilão finalizado',
            'O leilão para ' . $leilao . ' foi finalizado.'
        );

        if (!$sucesso) {
            throw new \DomainException('Erro ao enviar e-mail');
        }
    }
}