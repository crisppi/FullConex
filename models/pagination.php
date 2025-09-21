<?php
class pagination
{

    // NUMERO DE RESGISTROS POR PAGINA

    private $limite;

    //QUANTIDADE DE RESULTADOS 
    private $results;

    //QUANTIDADE DE PAGINAS 
    private $pages;

    //QUANTIDADE DE BLOCOS DE PAGINAS 
    private $bloco;

    //PAGINA ATUAL 
    private $currentPage;

    //PAGINA ATUAL 
    private $currentBloco;

    public function __construct($results, $currentPage = 1, $limite = 10)
    {
        //CONSTRUTOR DA CLASSE
        $this->results = $results;
        $this->limite = $limite;
        // $this->bloco = $bloco;
        $this->currentPage = (is_numeric($currentPage) and $currentPage > 0) ? $currentPage : 1;
        // $this->currentBloco = (is_numeric($currentBloco) and $currentBloco > 0) ? $currentBloco : 1;
        $this->calculate();
    }

    private function calculate()
    { // calcula o total de paginas
        $this->pages = $this->results > 0 ? ceil($this->results / $this->limite) : 1;

        $this->bloco = $this->results > 0 ? ceil($this->results / 5) : 1;

        //verifica se a pagina atual nao excede o numero de paginas
        $this->currentPage = $this->currentPage <= $this->pages ? $this->currentPage : $this->pages;

        //verifica se a pagina atual nao excede o numero de paginas
        $this->currentBloco = $this->currentBloco <= $this->bloco ? $this->currentPage : $this->bloco;
    }

    public function getLimit()
    {
        // retorna a clausula limite 
        $offSet = ($this->limite *  ($this->currentPage - 1));
        if (isset($qtdLinksPagina)) {
            $offSet = $offSet + 10;
            echo ($offSet);
        }
        return $offSet . ',' . $this->limite;
    }

    // retorna a opcoes de paginas disponiveis
    public function getPages()
    {
        if ($this->pages == 1) return [];
        // $b = 0;
        $paginas = [];
        isset($blocoNovo) ? $blocoNovo : $blocoNovo = 0;
        // print_r($blocoNovo);

        for ($i = 1; $i <= $this->pages; $i++) {

            $paginas[] = [
                'pg' => $i,
                'atual' => $i == $this->currentPage,
                $blocoNovo = $blocoNovo + 0.20,
                // ceil($blocoNovo),
                'bloco' => ceil($blocoNovo)
            ];
        };

        return $paginas;
    }
}
