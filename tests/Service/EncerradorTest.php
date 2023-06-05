<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

// class para duble de teste manualmente criado.

// class LeilaoDaoMocks extends LeilaoDao
// {
//     private $leiloes = [];	

//     public function salva(Leilao $leilao): void
//     {
//         $this->leiloes[] = $leilao;
//     }

//     public function recuperarNaoFinalizados(): array
//     {
//         return array_filter($this->leiloes, function (Leilao $leilao) {
//             return !$leilao->estaFinalizado();
//         });
//     }

//     public function atualiza(Leilao $leilao): void
//     {
//         // Não faz nada
//     }

//     public function recuperarFinalizados(): array
//     {
//         return array_filter($this->leiloes, function (Leilao $leilao) {
//             return $leilao->estaFinalizado();
//         });
//     }

// }

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados ()
    {

        $cruze2023 = new Leilao(
            'Cruze 2023',
            new \DateTimeImmutable('8 days ago')
        );

        $hb20 = new Leilao(
            'HB20 2023',
            new \DateTimeImmutable('10 days ago')
        );

        // $leilaoDao = new LeilaoDaoMocks(); // class criada manualmente em cima da class EncerradorTest.

        $leilaoDao = $this->createMock(LeilaoDao::class); // class criada pelo PHPUnit Automatizado.

        // personalizar o mock
        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->setConstructorArgs([new \PDO('sqlite::memory:')]) // passando o construtor da class LeilaoDao
        //     ->getMock();

        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$cruze2023, $hb20]);
        $leilaoDao->method('recuperarFinalizados')->willReturn([$cruze2023, $hb20]);
        $leilaoDao->expects($this->exactly(2))->method('atualiza')->withConsecutive([$cruze2023], [$hb20]);

        // $leilaoDao->salva($cruze2023);
        // $leilaoDao->salva($hb20);

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
        // self::assertEquals('Cruze 2023', $leiloes[0]->recuperarDescricao());
        // self::assertEquals('HB20 2023', $leiloes[1]->recuperarDescricao());


    }
}