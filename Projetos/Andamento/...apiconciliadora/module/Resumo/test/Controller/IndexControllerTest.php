<?php

namespace ResumoTest\Controller;

use Application\Entity\Count;
use Application\Controller\Plugin\ResultSetToArray;
use Application\Controller\AbstractHttpControllerTestCase;
use Exception;
use Resumo\Controller\IndexController;
use Resumo\Exception\RuntimeException;
use Resumo\Entity\Resumo;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Zend\Hydrator\ClassMethods;
use Zend\Db\ResultSet\HydratingResultSet;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    /**
     * @var \Resumo\Service\Resumo
     */
    private $service;

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../../config/application.config.php');

        $this->service = $this->getMockBuilder(\Resumo\Service\Resumo::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'selecionar',
                'contar'
            ])
            ->getMock();

        parent::setUp();
        $this->getApplicationServiceLocator()->setService(\Resumo\Service\Resumo::class, $this->service);
    }


    public function testGetList()
    {
        $resultSetToArray = new ResultSetToArray();

        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine('User-Device', 'mobile');

        $resultSet = new HydratingResultSet(new ClassMethods(), new Resumo());
        $resultSet->initialize([['id' => 1], ['id' => 1]]);

        $this->service->expects($this->once())->method('selecionar')->willReturn($resultSet);

        $resultadoContar = new Count([
            'pagina' => 1,
            'totalRegistros' => 2,
            'quantidadePaginas' => 20,
        ]);

        $this->service->expects($this->once())->method('contar')->willReturn($resultadoContar);

        $retorno = new JsonModel([
            'data' => $resultSetToArray($resultSet),
            'count' => $resultadoContar->toArray(),
        ]);

        $this->dispatch('/resumo', 'GET', ['colunas' => ['id', 'tipo']]);
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode($retorno->getVariables()), $this->getResponse()->getBody());
    }

    public function testGetListInvalido()
    {
        $this->service->expects($this->once())
                ->method('selecionar')
                ->will($this->throwException(new RuntimeException('', 404)));

        $this->dispatch('/resumo', 'GET', ['colunas' => ['id', 'tipo']]);
        $this->assertResponseStatusCode(404);

        $resultado = Json::decode($this->getResponse()->getBody());
        $this->assertEquals('', $resultado->message);
    }

}