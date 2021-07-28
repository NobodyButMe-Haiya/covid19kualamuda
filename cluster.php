<?php
//  note from git
/**
 * Cluster analysis
 * cluster: unique textual identifier of cluster; nomenclature does not necessarily signify address
 * state and district: geographical epicentre of cluster, if localised; inter-district and inter-state clusters are possible and present in the dataset
 * date_announced: date of declaration as cluster
 * date_last_onset: most recent date of onset of symptoms for individuals within the cluster. note that this is distinct from the date on which said individual was tested, and the date on which their test result was received; consequently, today's date may not necessarily be present in this column.
 * category: classification as per variable cluster_x above
 * status: active or ended
 * cases_new: number of new cases detected within cluster in the 24h since the last report
 * cases_total: total number of cases traced to cluster
 * cases_active: active cases within cluster
 * tests: number of tests carried out on individuals within the cluster; denominator for computing a cluster's current positivity rate
 * icu: number of individuals within the cluster currently under intensive care
 * deaths: number of individuals within the cluster who passed away due to COVID-19
 * recovered: number of individuals within the cluster who tested positive for and subsequently recovered from COVID-19
 */
// get data from the server or local path
$server_path = "https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/main/epidemic/clusters.csv";
$local_path = "clusters.csv";

// temp will be using local path
$path = $server_path;

$title_info = [
    ["title" => "Kluster", "description" => "unique textual identifier of cluster; nomenclature does not necessarily signify address"],
    ["title" => "Negeri", "description" => "geographical epicentre of cluster, if localised; inter-district and inter-state clusters are possible and present in the dataset"],
    ["title" => "Daerah", "description" => "geographical epicentre of cluster, if localised; inter-district and inter-state clusters are possible and present in the dataset"],

    ["title" => "Tarikh Mula Kluster", "description" => "date of declaration as cluster"],
    ["title" => "Tarikh Terakhir", "description" => "most recent date of onset of symptoms for individuals within the cluster. note that this is distinct from the date on which said individual was tested, and the date on which their test result was received; consequently, today's date may not necessarily be present in this column."],
    ["title" => "Kategori", "description" => "classification as per variable cluster_x above"],
    ["title" => "Status", "description" => "active or ended"],
    ["title" => "Kes Baru(24) ", "description" => "number of new cases detected within cluster in the 24h since the last report"],
    ["title" => "Jumlah Baru", "description" => "total number of cases traced to cluster"],
    ["title" => "Jumlah Aktif", "description" => "active cases within cluster"],
    ["title" => "Ujian", "description" => "number of tests carried out on individuals within the cluster; denominator for computing a cluster's current positivity rate"],
    ["title" => "ICU", "description" => "number of individuals within the cluster currently under intensive care"],
    ["title" => "Kematian", "description" => "number of individuals within the cluster who passed away due to COVID-19"],
    ["title" => "Sembuh", "description" => "number of individuals within the cluster who tested positive for and subsequently recovered from COVID-19"],
];

// recursive the array
$row = 0;
$data = [];
$cluster_info = [];
// category_array
$category_array = [];
$status_array = [];
// sometimes we want to distinct kuala muda  but not the sub cluster
$district_array = [];
if (($handle = fopen($path, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        for ($c = 0; $c < $num; $c++) {
            $cluster_info[$row][$c] = $data[$c];
        }
        $row++;
    }
    fclose($handle);
}
// recursive back  filter
$filtered_array = [];
$j = 0;
for ($i = 0; $i < count($cluster_info); $i++) {
    if (strpos($cluster_info[$i][2], "Kuala Muda")) {
        $filtered_array[$j] = $cluster_info[$i];
        $category_array[] = $cluster_info[$i][5];
        $status_array[] = $cluster_info[$i][6];
        $district_array[] = $cluster_info[$i][3];
        $j++;
    }
}
//echo "<pre>";
//var_dump($filtered_array);
//echo "</pre>";
// category_array
$category_array = array_unique($category_array);
$status_array = array_unique($status_array);
// sometimes we want to distinct kuala muda  but not the sub cluster
$district_array = array_unique($district_array);
?>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Analisa data covid-19 di Kedah berdasarkan kluster terkini </title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
</head>
<body>
<div class="container">

    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
        <tr>
            <th>#</th>
            <?php for ($i = 0; $i < count($title_info); $i++) { ?>
                <th scope="col"
                    title="<?php echo $title_info[$i]["description"] ?>"><?php echo $title_info[$i]["title"] ?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php for ($i = 0; $i < count($filtered_array); $i++) { ?>
            <tr>
                <th scope="row"><?php echo $i; ?></th>
                <?php for ($j = 0; $j < count($filtered_array[$i]); $j++) {
                    if ($j == 5) {
                        switch ($filtered_array[$i][$j]) {
                            case "religious":
                                echo "<td>Keagamaan</td>\n";

                                break;
                            case "community":
                                echo "<td>Komuniti</td>\n";

                                break;
                            case "highRisk":
                                echo "<td>Risiko Tinggi</td>\n";

                                break;
                            case "workplace":
                                echo "<td>Tempat Kerja</td>\n";

                                break;
                            case "detentionCentre":
                                echo "<td>Tahanan</td>\n";

                                break;
                            case "education":
                                echo "<td>Tempat Belajar</td>\n";

                                break;
                            default:
                                echo "<td>Salah</td>\n";
                                break;
                        }
                    } else if ($j == 6) {
                        switch ($filtered_array[$i][$j]) {
                            case "active":
                                echo "<td>Aktif</td>\n";
                                break;
                            case "ended":
                                echo "<td>Tamat</td>\n";
                                break;
                            default:
                                echo "<td>Salah</td>\n";
                                break;
                        }
                    } else {
                        if(is_numeric($filtered_array[$i][$j])) {
                            echo "<td style=\"text-align: right\">" . number_format($filtered_array[$i][$j]) . "</td>\n";
                        }else  if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $filtered_array[$i][$j], $split)) {
                            echo "<td style=\"text-align: center\"><pre>".$split[3]."-".$split[2]."-".$split[1]."</pre></td>\n";

                        }else {
                            echo "<td>" . $filtered_array[$i][$j] . "</td>\n";
                        }
                    }
                    ?>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

<script>
    $(document).ready(function() {
        const table = $('#example').DataTable( {
            "iDisplayLength": 50
        } );
        table.order( [[ 7, 'asc' ]]).draw();
    } );
</script>
</body>
</html>
