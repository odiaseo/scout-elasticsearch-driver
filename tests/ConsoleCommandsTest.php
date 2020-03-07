<?php

namespace SynergyScoutElastic;

use Elasticsearch\Namespaces\IndicesNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SynergyScoutElastic\Client\ClientInterface;
use SynergyScoutElastic\Console\IndexConfiguratorMakeCommand;
use SynergyScoutElastic\Console\SearchableModelMakeCommand;
use SynergyScoutElastic\Console\SearchStrategyMakeCommand;
use SynergyScoutElastic\Stubs\ElasticIndexCreateCommandStub;
use SynergyScoutElastic\Stubs\ElasticIndexDropCommandStub;
use SynergyScoutElastic\Stubs\ElasticIndexUpdateCommandStub;
use SynergyScoutElastic\Stubs\ElasticUpdateMappingCommandStub;
use SynergyScoutElastic\Stubs\IndexConfiguratorStub;
use SynergyScoutElastic\Stubs\ModelStub;

class ConsoleCommandsTest extends TestCase
{
    public function testIfTheCreateIndexCommandBuildsCorrectResponse()
    {
        $payload = [
            'index' => 'test_index',
            'body'  => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'test_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace'
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    '_default_' => [
                        'properties' => [
                            'test_default_field' => [
                                'type'     => 'string',
                                'analyzer' => 'test_analyzer'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $indices = $this->prophesize(IndicesNamespace::class);
        $client  = $this->prophesize(ClientInterface::class);
        $output  = $this->prophesize(OutputInterface::class);

        $client->indices()->willReturn($indices->reveal());
        $indices->create($payload)->shouldBeCalled();

        $input   = $this->getMockInputs(['index-configurator' => IndexConfiguratorStub::class]);
        $command = new ElasticIndexCreateCommandStub($client->reveal(), $input, $output->reveal());

        $command->handle();
        $this->addToAssertionCount(1);
    }

    private function getMockInputs(array $arguments)
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getArguments()->willReturn($arguments);
        $input->getOption('name')->willReturn('test_index');

        foreach ($arguments as $key => $value) {
            $input->getArgument($key)->willReturn($value);

        }

        return $input->reveal();
    }

    public function testIfTheUpdateIndexCommandBuildsCorrectResponse()
    {
        $payload = [
            'index' => 'test_index',
            'body'  => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'test_analyzer' => [
                                'type'      => 'custom',
                                'tokenizer' => 'whitespace'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mapping = [
            'index' => 'test_index',
            'type'  => '_default_',
            'body'  => [
                '_default_' => [
                    'properties' => [
                        'test_default_field' => [
                            'type'     => 'string',
                            'analyzer' => 'test_analyzer'
                        ]
                    ]
                ]
            ]
        ];

        $indices = $this->prophesize(IndicesNamespace::class);
        $client  = $this->prophesize(ClientInterface::class);
        $output  = $this->prophesize(OutputInterface::class);

        $client->indices()->willReturn($indices->reveal());
        $indices->putSettings($payload)->willReturn(null);
        $indices->putMapping($mapping)->willReturn(null);
        $indices->close(["index" => "test_index"])->willReturn(null);
        $indices->open(["index" => "test_index"])->willReturn(null);
        $indices->exists(["index" => "test_index"])->willReturn(true);

        $input   = $this->getMockInputs(['index-configurator' => IndexConfiguratorStub::class]);
        $command = new ElasticIndexUpdateCommandStub($client->reveal(), $input, $output->reveal());

        $command->handle();
        $this->addToAssertionCount(1);
    }

    public function testIfTheDropIndexCommandBuildsCorrectResponse()
    {

        $indices = $this->prophesize(IndicesNamespace::class);
        $client  = $this->prophesize(ClientInterface::class);
        $output  = $this->prophesize(OutputInterface::class);

        $input   = $this->getMockInputs(['index-configurator' => IndexConfiguratorStub::class]);
        $command = new ElasticIndexDropCommandStub($client->reveal(), $input, $output->reveal());

        $client->indices()->willReturn($indices->reveal());
        $indices->delete(["index" => "test_index"])->willReturn(null);
        $command->handle();

        $this->addToAssertionCount(1);
    }

    public function testIfTheUpdateMappingCommandBuildsCorrectResponse()
    {
        $mapping = [
            'index' => 'test_index',
            'type'  => 'test_table',
            'body'  => [
                'test_table' => [
                    'properties' => [
                        'test_default_field' => [
                            'type'     => 'string',
                            'analyzer' => 'test_analyzer'
                        ],
                        'id'                 => [
                            'type'  => 'integer',
                            'index' => 'not_analyzed'
                        ],
                        'test_field'         => [
                            'type'     => 'string',
                            'analyzer' => 'standard'
                        ]
                    ]
                ]
            ]
        ];
        $indices = $this->prophesize(IndicesNamespace::class);
        $client  = $this->prophesize(ClientInterface::class);
        $output  = $this->prophesize(OutputInterface::class);

        $input   = $this->getMockInputs(['model' => ModelStub::class]);
        $command = new ElasticUpdateMappingCommandStub($client->reveal(), $input, $output->reveal());

        $client->indices()->willReturn($indices->reveal());
        $indices->putMapping($mapping)->shouldBeCalled();
        $command->handle();

        $this->addToAssertionCount(1);

    }

    /**
     * @param $className
     * @dataProvider commandNameProvider
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testStubFileExists($className)
    {
        $command = app()->make($className);
        $this->assertInstanceOf($className, $command);
        $this->assertFileExists($command->getStub());
    }

    public function commandNameProvider()
    {
        return [
            [SearchableModelMakeCommand::class],
            [IndexConfiguratorMakeCommand::class],
            [SearchStrategyMakeCommand::class],
        ];
    }
}