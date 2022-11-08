<?php 

    $produtos = GetProdutos();

    if($produtos != 0){
        EnviaProdutos($produtos);
    }

    function EnviaProdutos($produtos){
        $json = GeraJson($produtos);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://seupreco.validasolucoes.com.br/api/CadastraProdutoNovo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }

    function GeraJson($produtos){
        include('config.php');

        foreach($produtos as $p){
            $itens[] = [
                'codigo'    => $p['cadp_codigo'],
                'barras'    => $p['cadp_codigobarra'],
                'descricao' => $p['cadp_descricao'].' '.$p['cadp_complemento'],
                'status'    => '0',
                'emb'       => $p['cade_tpemb'],
                'qtd_emb'   => $p['cade_qemb'],
                'pr_venda'  => $p['cade_prvenda'],
                'pr_custo'  => $p['cade_ctdesembolso']
            ];
        }

        $array = [
            'cliente'  => $cnpj,
            'produtos' => $itens
        ];

        return json_encode($array);
    }

    function GetProdutos(){
        include('config.php');

        $data = date('Y-m-d', strtotime('-1 days', strtotime(date('Y-m-d'))));

        $con_string_prime = ConfigConexaoPrime();
        
        $conn = pg_connect($con_string_prime);
        $a = pg_query($conn, "SELECT cadp_codigo, cadp_descricao, cadp_complemento, cadp_codigobarra, cade_prvenda, cade_ctdesembolso, cade_tpemb, cade_qemb
                                FROM cadprod
                                INNER JOIN cadprodemp ON cade_codigo = cadp_codigo
                                INNER JOIN empresas ON empr_codigo = cade_codempresa
                                WHERE empr_cnpjcpf = '$cnpj' AND cadp_dtcadastro = '$data' AND cade_ativo = 'S'
                                ORDER BY cadp_codigo");
        $result = pg_fetch_all($a);

        if(empty($result)){
            $result = 0;
        }

        return $result;
    }

    function ConfigConexaoPrime(){
        include('config.php');
    
        $con_string = 'host=' . $host . ' port=' . $porta . ' dbname=' . $banco . ' user=' . $usuario . ' password=' . $senha;
    
        return $con_string;
    }


?>