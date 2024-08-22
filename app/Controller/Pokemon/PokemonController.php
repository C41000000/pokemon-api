<?php
namespace App\Controller\Pokemon;

use App\Controller\AbstractController;
use App\Service\Pokemon\Pokemon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\HttpServer\Contract\ResponseInterface;

class PokemonController extends AbstractController
{
    private Pokemon $pokemonService;
    private ResponseInterface $responseInterface;

    public function __construct(Pokemon $pokemonService, ResponseInterface $responseInterface)
    {
        parent::__construct();
        $this->pokemonService = $pokemonService;
        $this->responseInterface = $responseInterface;
    }

    /**
     * @return string
     */
    public function list():string
    {
        try{
            $pokemon =  $this->pokemonService->getPokemon();

            return $this->responseInterface->json([
                'sucess' => true,
                'list' => $pokemon
            ]);
        }
        catch (GuzzleException $ex){
            return $this->responseInterface->json([
                'error' => true,
                'message' => $ex->getMessage()
            ]) ;
        }
        catch (Exception $ex){
            return $this->responseInterface->json([
                'error' => true,
                'message' => $ex->getMessage()
            ]) ;
        }
    }
}