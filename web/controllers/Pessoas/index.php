<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';
require_once __DIR__.'/../../../src/utils.php';


use Symfony\Component\Validator\Constraints as Assert;

$app->match('/Pessoas/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->request->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'Codigo', 
		'Nome', 
		'Rua', 
		'Bairro', 
		'Cidade', 
		'Complemento', 
		'id_uf', 
		'CEP', 
		'CPF', 
		'RG', 
		'Email', 
		'DataNascimento', 

    );
    
    $table_columns_type = array(
		'int', 
		'varchar(70)', 
		'varchar(100)', 
		'varchar(70)', 
		'varchar(70)', 
		'varchar(50)', 
		'int', 
		'char(9)', 
		'char(11)', 
		'char(10)', 
		'varchar(50)', 
		'date', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `Pessoas`" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `Pessoas`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		if( $table_columns_type[$i] != "blob") {
				$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
		} else {				if( !$row_sql[$table_columns[$i]] ) {
						$rows[$row_key][$table_columns[$i]] = "0 Kb.";
				} else {
						$rows[$row_key][$table_columns[$i]] = " <a target='__blank' href='menu/download?id=" . $row_sql[$table_columns[0]];
						$rows[$row_key][$table_columns[$i]] .= "&fldname=" . $table_columns[$i];
						$rows[$row_key][$table_columns[$i]] .= "&idfld=" . $table_columns[0];
						$rows[$row_key][$table_columns[$i]] .= "'>";
						$rows[$row_key][$table_columns[$i]] .= number_format(strlen($row_sql[$table_columns[$i]]) / 1024, 2) . " Kb.";
						$rows[$row_key][$table_columns[$i]] .= "</a>";
				}
		}

        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});




/* Download blob img */
$app->match('/Pessoas/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . Pessoas . " WHERE ".$idfldname." = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($rowid));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('menu_list'));
    }

    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header("Content-length: ".strlen( $row_sql[$fieldname] ));
    header('Expires: 0');
    header('Cache-Control: public');
    header('Pragma: public');
    ob_clean();    
    echo $row_sql[$fieldname];
    exit();
   
    
});



$app->match('/Pessoas', function () use ($app) {
    
	$table_columns = array(
		'Codigo', 
		'Nome', 
		'Rua', 
		'Bairro', 
		'Cidade', 
		'Complemento', 
		'id_uf', 
		'CEP', 
		'CPF', 
		'RG', 
		'Email', 
		'DataNascimento', 

    );

    $primary_key = "Codigo";	

    return $app['twig']->render('Pessoas/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('Pessoas_list');



$app->match('/Pessoas/create', function () use ($app) {
    
    $initial_data = array(
		'Nome' => '', 
		'Rua' => '', 
		'Bairro' => '', 
		'Cidade' => '', 
		'Complemento' => '', 
		'id_uf' => '', 
		'CEP' => '', 
		'CPF' => '', 
		'RG' => '', 
		'Email' => '', 
		'DataNascimento' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('Nome', 'text', array('required' => true));
	$form = $form->add('Rua', 'text', array('required' => false));
	$form = $form->add('Bairro', 'text', array('required' => false));
	$form = $form->add('Cidade', 'text', array('required' => false));
	$form = $form->add('Complemento', 'text', array('required' => false));
	$form = $form->add('id_uf', 'text', array('required' => true));
	$form = $form->add('CEP', 'text', array('required' => false));
	$form = $form->add('CPF', 'text', array('required' => true));
	$form = $form->add('RG', 'text', array('required' => false));
	$form = $form->add('Email', 'text', array('required' => false));
	$form = $form->add('DataNascimento', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `Pessoas` (`Nome`, `Rua`, `Bairro`, `Cidade`, `Complemento`, `id_uf`, `CEP`, `CPF`, `RG`, `Email`, `DataNascimento`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['Nome'], $data['Rua'], $data['Bairro'], $data['Cidade'], $data['Complemento'], $data['id_uf'], $data['CEP'], $data['CPF'], $data['RG'], $data['Email'], $data['DataNascimento']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Pessoas created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Pessoas_list'));

        }
    }

    return $app['twig']->render('Pessoas/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('Pessoas_create');



$app->match('/Pessoas/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Pessoas` WHERE `Codigo` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('Pessoas_list'));
    }

    
    $initial_data = array(
		'Nome' => $row_sql['Nome'], 
		'Rua' => $row_sql['Rua'], 
		'Bairro' => $row_sql['Bairro'], 
		'Cidade' => $row_sql['Cidade'], 
		'Complemento' => $row_sql['Complemento'], 
		'id_uf' => $row_sql['id_uf'], 
		'CEP' => $row_sql['CEP'], 
		'CPF' => $row_sql['CPF'], 
		'RG' => $row_sql['RG'], 
		'Email' => $row_sql['Email'], 
		'DataNascimento' => $row_sql['DataNascimento'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('Nome', 'text', array('required' => true));
	$form = $form->add('Rua', 'text', array('required' => false));
	$form = $form->add('Bairro', 'text', array('required' => false));
	$form = $form->add('Cidade', 'text', array('required' => false));
	$form = $form->add('Complemento', 'text', array('required' => false));
	$form = $form->add('id_uf', 'text', array('required' => true));
	$form = $form->add('CEP', 'text', array('required' => false));
	$form = $form->add('CPF', 'text', array('required' => true));
	$form = $form->add('RG', 'text', array('required' => false));
	$form = $form->add('Email', 'text', array('required' => false));
	$form = $form->add('DataNascimento', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `Pessoas` SET `Nome` = ?, `Rua` = ?, `Bairro` = ?, `Cidade` = ?, `Complemento` = ?, `id_uf` = ?, `CEP` = ?, `CPF` = ?, `RG` = ?, `Email` = ?, `DataNascimento` = ? WHERE `Codigo` = ?";
            $app['db']->executeUpdate($update_query, array($data['Nome'], $data['Rua'], $data['Bairro'], $data['Cidade'], $data['Complemento'], $data['id_uf'], $data['CEP'], $data['CPF'], $data['RG'], $data['Email'], $data['DataNascimento'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'Pessoas edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('Pessoas_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('Pessoas/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('Pessoas_edit');


$app->match('/Pessoas/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `Pessoas` WHERE `Codigo` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `Pessoas` WHERE `Codigo` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'Pessoas deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('Pessoas_list'));

})
->bind('Pessoas_delete');



$app->match('/Pessoas/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'Codigo', 
		'Nome', 
		'Rua', 
		'Bairro', 
		'Cidade', 
		'Complemento', 
		'id_uf', 
		'CEP', 
		'CPF', 
		'RG', 
		'Email', 
		'DataNascimento', 

    );
    
    $table_columns_type = array(
		'int', 
		'varchar(70)', 
		'varchar(100)', 
		'varchar(70)', 
		'varchar(70)', 
		'varchar(50)', 
		'int', 
		'char(9)', 
		'char(11)', 
		'char(10)', 
		'varchar(50)', 
		'date', 

    );   

    $types_to_cut = array('blob');
    $index_of_types_to_cut = array();
    foreach ($table_columns_type as $key => $value) {
        if(in_array($value, $types_to_cut)){
            unset($table_columns[$key]);
        }
    }

    $columns_to_select = implode(',', array_map(function ($row){
        return '`'.$row.'`';
    }, $table_columns));
     
    $find_sql = "SELECT ".$columns_to_select." FROM `Pessoas`";
    $rows_sql = $app['db']->fetchAll($find_sql, array());
  
    $mpdf = new mPDF();

    $stylesheet = file_get_contents('../web/resources/css/bootstrap.min.css'); // external css
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML('.table {
    border-radius: 5px;
    width: 100%;
    margin: 0px auto;
    float: none;
}',1);

    $mpdf->WriteHTML(build_table($rows_sql));
    $mpdf->Output();
})->bind('Pessoas_downloadList');



