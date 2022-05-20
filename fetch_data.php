<?php
include('../../assets/config/db.inc.php');

$output= array();

$sql = "select * from jobs";
$totalQuery = mysqli_query($con,$sql);

$total_all_rows = mysqli_num_rows($totalQuery);
// DB table to use


// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    0 => 'id',
    1 => 'Job_Name',
    2 => 'Job_Category',
    3 => 'Job_Type',
    4 => 'From_Company',
    5 => 'Address',
    6 => 'salary',

);

if(isset($_POST['search']['value']))
{
    $search_value = $_POST['search']['value'];
    $sql .= " WHERE Job_Name like '%".$search_value."%'";
    $sql .= " OR Job_Category like '%".$search_value."%'";
    $sql .= " OR From_Company like '%".$search_value."%'";
    $sql .= " OR salary like '%".$search_value."%'";
    $sql .= " OR Address like '%".$search_value."%'";
}
if(isset($_POST['order']))
{
    $column_name = $_POST['order'][0]['column'];
    $order = $_POST['order'][0]['dir'];
    $sql .= " ORDER BY ".$columns[$column_name]." ".$order."";
}
else
{
    $sql .= " ORDER BY id desc";
}

if($_POST['length'] != -1)
{
    $start = $_POST['start'];
    $length = $_POST['length'];
    $sql .= " LIMIT  ".$start.", ".$length;
}

$query = mysqli_query($con,$sql);
$count_rows = mysqli_num_rows($query);
$data = array();

while($row = mysqli_fetch_assoc($query))
{
    $sub_array = array();
    $sub_array[] = $row['id'];
    $sub_array[] = $row['Job_Name'];
    $sub_array[] = $row['Job_Category'];
    $sub_array[] = $row['Job_Type'];
    $sub_array[] = $row['From_Company'];
    $sub_array[] = $row['Address'];
    $sub_array[] = $row['salary'];
    $sub_array[] = '<a href="javascript:void();" data-id="'.$row['id'].'"  class="btn btn-info btn-sm AssBtn" >Request</a>';
    $data[] = $sub_array;

}

$output = array(
    'draw'=> (int)$_POST['draw'],
    'recordsTotal' =>$count_rows ,
    'recordsFiltered'=> $total_all_rows,
    'data'=>$data,
);
echo  json_encode($output);