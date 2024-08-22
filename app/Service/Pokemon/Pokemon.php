<?php

namespace App\Service\Pokemon;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\WaitGroup;
class Pokemon
{
    private const LIMIT_POKEMON = 5;
    private string $apiBaseUrl;
    private Client $guzzleClient;

    public function __construct(Client $guzzleClient)
    {
        $this->apiBaseUrl = "https://pokeapi.co/api/v2";
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @return array
     * @throws GuzzleException
     * @throws Exception
     */
    public function getPokemon(): array
    {
        $offset = rand(0, 500);
        $response = $this->guzzleClient->get($this->apiBaseUrl . "/pokemon?offset=" . $offset. "&limit=" . self::LIMIT_POKEMON);
        $httpCode = $response->getStatusCode();

        if($httpCode != 200){
            throw new Exception("Ocorreu um erro ao pesquisar pokemons");
        }

        $content =  $response->getBody()->getContents();
        $data = json_decode($content, true);
        $arrayPokemon = $data['results'];
        $waitGroup = new WaitGroup();
        $coroutines = [];

        foreach($arrayPokemon as  &$pokemon){
            $waitGroup->add();
            $coroutines[] = Coroutine::create(function () use (&$pokemon,  $waitGroup){

                try{
                    $pokemon['details'] = $this->getPokemonInfo($pokemon['url']);
                    $subject = $this->sliceMoves($pokemon['details']['moves'], rand(5,15), self::LIMIT_POKEMON);
                    $pokemon['details']['moves'] = $this->getMoves($subject);
                }
                catch (Exception $e){
                    $pokemon['test'] = $e->getMessage();
                    $pokemon['details'] = ['erro1' => $e->getMessage()];
                } finally {
                    $waitGroup->done();
                }
            });
        }
        $waitGroup->wait();
        return $this->filter($arrayPokemon);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getPokemonInfo(string $url)
    {
        $response = $this->guzzleClient->get($url);
        $content = $response->getBody()->getContents();
        $info = json_decode($content, true);
        if(json_last_error() !== JSON_ERROR_NONE){
            throw new Exception("Ocorreu um erro ao identificar os detalhes do pokemon");
        }
        return $info;
    }

    /**
     * @param array $moves
     * @return array
     */
    public function getMoves (array $moves): array
    {
        $waitGroup = new WaitGroup();
        $arrayMovesDetail = [];

        foreach($moves as &$move){
            $waitGroup->add();
            Coroutine::create(function () use ($move, $waitGroup,  &$arrayMovesDetail){
                try{
                    $url = $move['move']['url'];
                    $response = $this->guzzleClient->get($url);
                    $content = $response->getBody()->getContents();
                    $move = json_decode($content, true);

                    if(json_last_error() !== JSON_ERROR_NONE){
                        throw new Exception("Ocorreu um erro ao buscar detalhes das habilidades!");
                    }
                    $arrayMovesDetail[$url] = $move;
                }
                catch (Exception $e){
                    $arrayMovesDetail[$url] = ['erro' => $e->getMessage()];
                } finally {
                    $waitGroup->done();
                }
            });
        }
        $waitGroup->wait();

        return $arrayMovesDetail;
    }

    /**
     * @param array $moves
     * @param int $offset
     * @param int $limite
     * @return array
     */
    public function sliceMoves(array $moves, int $offset, int $limite): array
    {
        return array_slice($moves, $offset, $limite);
    }

    /**
     * @param array $arrayPokemon
     * @return array
     */
    public function filter(array $arrayPokemon): array
    {
        $newArray = [];
        foreach ($arrayPokemon as $pokemon) {
            $newArray[] = [
                'name' => $pokemon['name'],
                'moves' => $pokemon['details']['moves'] ?: [],
                'sprites' => $pokemon['details']['sprites'] ?: [],
            ];
        }
        return $newArray;
    }
}