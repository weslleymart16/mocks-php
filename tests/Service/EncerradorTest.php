<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\MockObject\MockObject;
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
    private $encerrador;
    private $enviadorEmail;
    private $leilaoCruze2023;
    private $leilaoHb20;

    public function setUp():void
    {

        $this->leilaoCruze2023 = new Leilao(
            'Cruze 2023',
            new \DateTimeImmutable('8 days ago')
        );

        $this->leilaoHb20 = new Leilao(
            'HB20 2023',
            new \DateTimeImmutable('10 days ago')
        );

        // $leilaoDao = new LeilaoDaoMocks(); // class criada manualmente em cima da class EncerradorTest.

        /**
         * @var LeilaoDao|MockObject $leilaoDao
         */
        $leilaoDao = $this->createMock(LeilaoDao::class); // class criada pelo PHPUnit Automatizado.

        // personalizar o mock
        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->setConstructorArgs([new \PDO('sqlite::memory:')]) // passando o construtor da class LeilaoDao
        //     ->getMock();

        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$this->leilaoCruze2023, $this->leilaoHb20]);
        $leilaoDao->method('recuperarFinalizados')->willReturn([$this->leilaoCruze2023, $this->leilaoHb20]);
        $leilaoDao->expects($this->exactly(2))->method('atualiza')->withConsecutive([$this->leilaoCruze2023], [$this->leilaoHb20]);

        // $leilaoDao->salva($cruze2023);
        // $leilaoDao->salva($hb20);

        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
        /**
         * @var EnviadorEmail|MockObject $enviadorEmail
         */
        $enviadorEmail = $this->enviadorEmail;

        $this->encerrador = new Encerrador($leilaoDao, $enviadorEmail);
        
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados ()
    {
        $this->encerrador->encerra();

        $leiloes = [$this->leilaoCruze2023, $this->leilaoHb20];
        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
        // self::assertEquals('Cruze 2023', $leiloes[0]->recuperarDescricao());
        // self::assertEquals('HB20 2023', $leiloes[1]->recuperarDescricao());


    }

    public function testeDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
        $e = new \DomainException('Erro ao enviar e-mail');
        $this->enviadorEmail->expects($this->exactly(2))->method('notificarTerminoLeilao')->willThrowException($e);

        $this->encerrador->encerra();

    }

    public function testSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))->method('notificarTerminoLeilao')->willReturnCallback(function (Leilao $leilao) {
            static::assertTrue($leilao->estaFinalizado());
        });

        $this->encerrador->encerra();
    }
}