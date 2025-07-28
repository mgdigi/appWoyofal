<?php

class Achat{

    private int $id;
    private  string $reference;
    private string $code;

    private string $date;
    private float $prix;
    private float $nombreKwt;

    public function __construct($id = 0, $reference = '', $code = '', $date = '', $prix = 0.0, $nombreKwt = 0.0){
        $this->id = $id;
        $this->reference = $reference;
        $this->code = $code;
        $this->date = $date;
        $this->prix = $prix;
        $this->nombreKwt = $nombreKwt;
    }
 


}