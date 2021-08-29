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
$server_path_cases = "https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/main/epidemic/cases_state.csv";
$server_path_death = "https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/main/epidemic/deaths_state.csv";
$server_path_icu = "https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/main/epidemic/icu.csv";
$local_path = "clusters.csv";

// temp will be using local path
$path = $server_path;

$title_info = [
    ["title" => "Kluster", "description" => "unique textual identifier of cluster; nomenclature does not necessarily signify address"],
    ["title" => "Negeri", "description" => "geographical epicentre of cluster, if localised; inter-district and inter-state clusters are possible and present in the dataset"],
    ["title" => "Daerah", "description" => "geographical epicentre of cluster, if localised; inter-district and inter-state clusters are possible and present in the dataset"],

    ["title" => "Tarikh Mula", "description" => "date of declaration as cluster"],
    ["title" => "Tarikh Terakhir", "description" => "most recent date of onset of symptoms for individuals within the cluster. note that this is distinct from the date on which said individual was tested, and the date on which their test result was received; consequently, today's date may not necessarily be present in this column."],
    ["title" => "Kategori", "description" => "classification as per variable cluster_x above"],
    ["title" => "Status", "description" => "active or ended"],
    ["title" => " Baru(24) ", "description" => "number of new cases detected within cluster in the 24h since the last report"],
    ["title" => "Jumlah", "description" => "total number of cases traced to cluster"],
    ["title" => "Aktif", "description" => "active cases within cluster"],
    ["title" => "Ujian", "description" => "number of tests carried out on individuals within the cluster; denominator for computing a cluster's current positivity rate"],
    ["title" => "ICU", "description" => "number of individuals within the cluster currently under intensive care"],
    ["title" => "Kematian", "description" => "number of individuals within the cluster who passed away due to COVID-19"],
    ["title" => "Sembuh", "description" => "number of individuals within the cluster who tested positive for and subsequently recovered from COVID-19"],
];

// recursive the array
$row = 0;
$data = [];
$cluster_info = [];
$cluster_info_outside_district = [];
// category_array
$category_array = [];
$status_array = [];

$district_array = [];
if (($handle = fopen($path, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
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
// future if we want to drill down .
$state_name = [];
$j = 0;
$o = 0;
$sumNew = 0;
$sumTotal = 0;
$sumActive = 0;
$sumTest = 0;
$sumIcu = 0;
$sumDeath = 0;
$sumIcu = 0;
$sumRecover = 0;

$sumReligious = 0;
$sumCommunity = 0;
$sumHighRisk = 0;
$sumWorkplace = 0;
$sumDetentionCentre = 0;
$sumEducation = 0;

$sumActiveCluster = 0;
$sumActiveDistrictCluster = 0;
$sumEndedCluster = 0;
$sumEndedDistrictCluster = 0;
$findMe = "Kedah";
//$findMe = "Negeri Sembilan";
for ($i = 0; $i < count($cluster_info); $i++) {

    $state_name[] = $cluster_info[$i][0];
    $pos = strpos($cluster_info[$i][1], $findMe);
    if ($pos !== false) {
        $filtered_array[$j] = $cluster_info[$i];
        $category_array[] = $cluster_info[$i][5];
        $status_array[] = $cluster_info[$i][6];
        $district_array[] = $cluster_info[$i][3];
        $j++;
        $sumNew += $cluster_info[$i][7];
        $sumTotal += $cluster_info[$i][8];
        $sumActive += $cluster_info[$i][9];
        $sumTest += $cluster_info[$i][10];
        $sumIcu += $cluster_info[$i][11];

        $sumDeath += $cluster_info[$i][12];
        $sumRecover += $cluster_info[$i][13];

        // sometimes we want to make data useful by category .crosstab much easier but we not in sql
        switch ($cluster_info[$i][5]) {
            case "religious":
                $sumReligious++;

                break;
            case "community":
                $sumCommunity++;

                break;
            case "highRisk":
                $sumHighRisk++;

                break;
            case "workplace":
                $sumWorkplace++;

                break;
            case "detentionCentre":
                $sumDetentionCentre++;

                break;
            case "education":
                $sumEducation++;

                break;
        }

        switch ($cluster_info[$i][6]) {
            case "active":
                $sumActiveCluster++;
                break;
            case "ended":
                $sumEndedCluster++;
                break;
        }

    }

}
//var_dump($cluster_info_outside_district);
//exit();
//echo "<pre>";
//var_dump($filtered_array);
//echo "</pre>";
// category_array
$category_array = array_unique($category_array);
//echo  "<!---  ".var_export($category_array)." -->";

$status_array = array_unique($status_array);
//echo  "<!---  ".var_export($status_array)." -->";

// sometimes we want to distinct kuala muda  but not the sub cluster
$district_array = array_unique($district_array);

//echo  "<!---  ".var_export($district_array)." -->";


$str = "";
$totalLocal = 0;
$totalImport = 0;
if (($handle = fopen($server_path_cases, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
        $pos = strpos($data[1], $findMe);
        if ($pos !== false) {
            $str .= "{date:\"" . $data[0] . "\",local:" . intval($data[2]) . ",import:" . intval($data[3]) . "},\n";
            $totalLocal += intval($data[2]);
            $totalImport += intval($data[3]);
        }

        $row++;
    }
    fclose($handle);
}
$totalDeath = 0;
$totalBid = 0;
$strDeath = "";
if (($handle = fopen($server_path_death, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
        $pos = strpos($data[1], $findMe);
        if ($pos !== false) {
            $strDeath .= "{date:\"" . $data[0] . "\",death:" . intval($data[2]) . ",bid:" . intval($data[3]) . "},\n";
            $totalDeath += intval($data[2]);
            $totalBid += intval($data[3]);
        }

        $row++;
    }
    fclose($handle);
}

$strIcu  = "";
if (($handle = fopen($server_path_icu, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
        $pos = strpos($data[1], $findMe);
        if ($pos !== false) {
            $percent = intval(($data[8]/$data[4]) * 100);
            $percentAll = intval((($data[8]+$data[10])/$data[4]) * 100);
            $strIcu.="{date:\"".$data[0]."\",icuBed:".intval($data[4]).",icuBedCovid19:".intval($data[8]).",percentage:".$percent.",percentageAll:".$percentAll."},\n";

        }

        $row++;
    }
    fclose($handle);
}
?>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Analisa data covid-19 di Kedah berdasarkan kluster terkini </title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet"
          id="bootstrap-css">
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.25/sorting/datetime-moment.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js"
            integrity="sha512-asxKqQghC1oBShyhiBwA+YgotaSYKxGP1rcSYTDrB0U6DxwlJjU59B67U8+5/++uFjcuVM8Hh5cokLjZlhm3Vg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script async
            src="https://www.googletagmanager.com/gtag/js?id=UA-129654074-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'UA-129654074-1');
    </script>
</head>
<body>
<div class="container-fluid" style="background: #D3D3D3">

    <h1>Analisa data covid-19 di Kedah berdasarkan kluster terkini </h1>
    <br/>
    <h2>
        <span style="color:red">** amaran  dilarang share ke sumber telegram palsu</span></h2>
    <br/>

    <div class="row align-items-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Kes Tempatan (Kedah)
                </div>
                <div class="card-body">
                    <span style="font-size: 24px;text-align: center">
                        <?php echo number_format($totalLocal); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Kes Import (Kedah)
                </div>
                <div class="card-body">
                    <span style="font-size: 24px;text-align: center">
                        <?php echo number_format($totalImport); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <div style="height: 250px;padding: 10px;background-color: white">
        <canvas id="kedahCases" height="250" width="250"></canvas>
    </div>
    <br/>

    <div class="row align-items-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Kematian (Kedah)
                </div>
                <div class="card-body">
                    <span style="font-size: 24px;text-align: center">
                        <?php echo number_format($totalDeath); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    BID (Kematian dibawa ke Hospital (Kedah)
                </div>
                <div class="card-body">
                    <span style="font-size: 24px;text-align: center">
                        <?php echo number_format($totalBid); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <div style="height: 250px;padding: 10px;background-color: white">
    <canvas id="kedahDeath" height="250" width="250"></canvas>
    </div>
    <br/>
    ** data ini hanya berdasarkan kkm github.kalau ada salah kena tanya kkm sendiri apa field yang patut. kami ambil apa ada sahaja
    <br />
    <br />
    <div style="height: 250px;padding: 10px;background-color: white">
        <canvas id="kedahIcu" height="250" width="250"></canvas>
    </div>
    <br />
    <h2>
        Statistik Aktif
    </h2>
    <br/>
    <div class="row align-items-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Aktif Kluster
                </div>
                <div class="card-body">
                    <span style="font-size: 24px;text-align: center">
                        <?php echo number_format($sumActiveCluster); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Tamat Kluster
                </div>
                <div class="card-body">
                    <span style="font-size: 24px;text-align: center ">
                        <?php echo number_format($sumEndedCluster); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <br/>

    <h2>Statisik Category</h2>
    <br/>
    <div class="row align-items-center">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Keagamaan
                </div>
                <div class="card-body">
                    <span style="font-size: 24px">
                        <?php echo number_format($sumReligious); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Komuniti
                </div>
                <div class="card-body">
                    <span style="font-size: 24px">
                        <?php echo number_format($sumCommunity); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Risiko Tinggi
                </div>
                <div class="card-body">
                    <span style="font-size: 24px">
                        <?php echo number_format($sumReligious); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <div class="row align-items-center">

        <div class="col">
            <div class="card">
                <div class="card-header">
                    Tempat Kerja
                </div>
                <div class="card-body">
                    <span style="font-size: 24px">
                        <?php echo number_format($sumWorkplace); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Tahanan
                </div>
                <div class="card-body">
                    <span style="font-size: 24px">
                        <?php echo number_format($sumDetentionCentre); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Belajar
                </div>
                <div class="card-body">
                    <span style="font-size: 24px">
                        <?php echo number_format($sumEducation); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <table id="kedah" class="table table-striped table-bordered" style="width:100%">
        <thead class="thead-dark">
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
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Agama</td>\n";

                                break;
                            case "community":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Komuniti</td>\n";

                                break;
                            case "highRisk":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Risiko Tinggi</td>\n";

                                break;
                            case "workplace":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Kerja</td>\n";

                                break;
                            case "detentionCentre":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Tahanan</td>\n";

                                break;
                            case "education":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Belajar</td>\n";

                                break;
                            default:
                                echo "<td>Salah</td>\n";
                                break;
                        }
                    } else if ($j == 6) {
                        switch ($filtered_array[$i][$j]) {
                            case "active":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Aktif</td>\n";
                                break;
                            case "ended":
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Tamat</td>\n";
                                break;
                            default:
                                echo "<td title=\"" . $title_info[$j]["description"] . "\">Salah</td>\n";
                                break;
                        }
                    } else {
                        if (is_numeric($filtered_array[$i][$j])) {
                            echo "<td title=\"" . $title_info[$j]["description"] . "\" style=\"text-align: right\">" . number_format($filtered_array[$i][$j]) . "</td>\n";
                        } else if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $filtered_array[$i][$j], $split)) {
                            echo "<td title=\"" . $title_info[$j]["description"] . "\" style=\"text-align: center\"><pre>" . $split[3] . "/" . $split[2] . "/" . $split[1] . "</pre></td>\n";

                        } else {
                            echo "<td title=\"" . $title_info[$j]["description"] . "\">" . $filtered_array[$i][$j] . "</td>\n";
                        }
                    }
                    ?>
                <?php } ?>
            </tr>
        <?php } ?>

        </tbody>
        <tfoot>
        <tr>
            <th colspan="8" style="text-align:right">Jumlah :</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tfoot>
        <tr>
            <th>#</th>
            <?php for ($i = 0; $i < count($title_info); $i++) { ?>
                <th scope="col"
                    title="<?php echo $title_info[$i]["description"] ?>"><?php echo $title_info[$i]["title"] ?></th>
            <?php } ?>
        </tr>
    </table>

    <br/>
    <br/>
    ** Dibawah hanya rumusan semua
    <br/>
    <table class="table table-striped table-bordered" style="width:100%">
        <tr>
            <th scope="row"><b>Baru : </b></th>
            <td id="sumNew" style="text-align: right"><?php echo number_format($sumNew); ?></td>
        </tr>
        <tr>
            <th scope="row"><b>Jumlah : </b></th>
            <td id="sumTotal" style="text-align: right"><?php echo number_format($sumTotal); ?></td>
        </tr>
        <tr>
            <th scope="row"><b>Aktif : </b></th>
            <td id="sumActive" style="text-align: right"><?php echo number_format($sumActive); ?></td>
        </tr>
        <tr>
            <th scope="row"><b>Ujian : </b></th>
            <td id="sumTest" style="text-align: right"><?php echo number_format($sumTest); ?></td>
        </tr>
        <tr>
            <th scope="row"><b>ICU : </b></th>
            <td id="sumIcu" style="text-align: right"><?php echo number_format($sumIcu); ?></td>
        </tr>
        <tr>
            <th scope="row"><b>Kematian</b></th>
            <td id="sumDeath" style="text-align: right"><?php echo number_format($sumDeath); ?></td>
        </tr>
        <tr>
            <th scope="row"><b>Pulih</b></th>
            <td id="sumRecover" style="text-align: right"><?php echo number_format($sumRecover); ?></td>
        </tr>
    </table>
    <blockquote>
        Sumber diperolehi dari : <a
                href="https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/">MoH-Malaysia
            GITHUB </a>
        <br/>
        Data hanya sekadar apa ada dan tidak ada berkaitan dengan server KKM
    </blockquote>
</div>

<script>


    const dataCases = [
        <?php echo $str; ?>
    ];
    const dataDeath = [
        <?php echo $strDeath; ?>
    ];

    const dataIcu = [
        <?php echo $strIcu; ?>
    ];
    new Chart("kedahCases", {
        type: 'line',
        data: {
            labels: dataCases.map(o => o.date),
            datasets: [{
                label: "Tempatan",
                fill: false,
                borderColor: "rgba(59, 89, 152, 1)",
                data: dataCases.map(o => o.local)
            }, {
                label: "Luar Negara",
                fill: false,
                borderColor: "rgba(255, 0, 0, 1)",
                data: dataCases.map(o => o.import)
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                xAxes: [{
                    type: 'date',
                    time: {
                        unit: 'day'
                    }
                }]
            }
        }
    });

    new Chart("kedahDeath", {
        type: 'line',
        data: {
            labels: dataDeath.map(o => o.date),
            datasets: [{
                label: "Kematian",
                fill: false,
                borderColor: "rgba(59, 89, 152, 1)",
                data: dataDeath.map(o => o.death)
            }, {
                label: "BID",
                fill: false,
                borderColor: "rgba(255, 0, 0, 1)",
                data: dataDeath.map(o => o.bid)
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                xAxes: [{
                    type: 'date',
                    time: {
                        unit: 'day'
                    }
                }]
            }
        }
    });

    new Chart("kedahIcu", {
        type: 'line',
        data: {
            labels: dataIcu.map(o => o.date ),
            datasets: [{
                label: "Katil Icu ",
                fill: false,
                borderColor: "rgba(59, 89, 152, 1)",
                data: dataIcu.map(o => o.icuBed)
            },{
                label: "Jumlah katil khas untuk covid",
                fill: false,
                borderColor: "rgba(255, 0, 0, 1)",
                data: dataIcu.map(o => o.icuBedCovid19)
            },{
                label: "%covid/ Jumlah Katil",
                fill: false,
                borderColor: "rgba(255, 255, 0, 1)",
                data: dataIcu.map(o => o.percentage)
            },{
                label: "%covid+%nonCovid/ Jumlah Katil",
                fill: false,
                borderColor: "rgba(145, 61, 136, 1)",
                data: dataIcu.map(o => o.percentageAll)
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive:true,
            scales: {
                xAxes: [{
                    type: 'date',
                    time: {
                        unit: 'day'
                    }
                }]
            }
        }
    });
    $.fn.dataTable.moment('DD/MM/YYYY');

    $(document).ready(function () {
        const tableKedah = $('#kedah').DataTable({
            "iDisplayLength": 100,
            "ordering": true,
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };
                // new
                const totalNew = api
                    .column(8)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotalNew = api
                    .column(8, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(8).footer()).html(
                    pageTotalNew.toLocaleString() + ' / ' + totalNew.toLocaleString() + ')'
                );
                // total
                const total = api
                    .column(9)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotal = api
                    .column(9, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(9).footer()).html(
                    pageTotal.toLocaleString() + ' / ' + total.toLocaleString() + ')'
                );
                // active
                const totalActive = api
                    .column(10)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotalActive = api
                    .column(10, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(10).footer()).html(
                    pageTotalActive.toLocaleString() + ' / ' + totalActive.toLocaleString() + ')'
                );
                // test

                const totalTest = api
                    .column(11)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotalTest = api
                    .column(11, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(11).footer()).html(
                    pageTotalTest.toLocaleString() + ' / ' + totalTest.toLocaleString() + ')'
                );
                // icu
                const totalIcu = api
                    .column(12)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotalIcu = api
                    .column(12, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(12).footer()).html(
                    pageTotalIcu.toLocaleString() + ' / ' + totalIcu.toLocaleString() + ')'
                );
                // death
                const totalDeath = api
                    .column(13)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotalDeath = api
                    .column(13, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(13).footer()).html(
                    pageTotalDeath.toLocaleString() + ' / ' + totalDeath.toLocaleString() + ')'
                );
                // recover
                const totalRecover = api
                    .column(14)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                const pageTotalRecover = api
                    .column(14, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(14).footer()).html(
                    pageTotalRecover.toLocaleString() + ' / ' + totalRecover.toLocaleString() + ')'
                );
            },
            "buttons": [
                'csv'
            ]
        });
        tableKedah.order([[7, 'asc'], [4, 'desc']]).draw();

    });
</script>
</body>
</html>
