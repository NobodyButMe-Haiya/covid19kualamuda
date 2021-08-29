<?php
/*/***
 * date: yyyy-mm-dd format; data correct as of 1200hrs on that date
 * state: name of state (present in state file, but not country file)
 * deaths
 * bid
 */
//date,state,beds_icu,beds_icu_rep,beds_icu_total,beds_icu_covid,vent,vent_port,icu_covid,icu_pui,icu_noncovid,vent_covid,vent_pui,vent_noncovid
// 0 ,  1,    2,       3,            4,             5,
// get data from the server or local path
$server_path_icu = "https://raw.githubusercontent.com/MoH-Malaysia/covid19-public/main/epidemic/icu.csv";
$local_path = "cases.csv";

// temp will be using local path
$path = $server_path_icu;

$row = 0;
$data = [];
$findMe = "Kedah";

$strIcu  = "";
if (($handle = fopen($path, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000)) !== FALSE) {
        $pos = strpos($data[1], $findMe);
        if ($pos !== false) {
            $strIcu.="{date:\"".$data[0]."\",icuBed:".intval($data[5]).",icuCovid:".((intval($data[10])/intval($data[5])) * 100)."},\n";

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

    <title>Analisa data covid-19 di Kedah  </title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet"
          id="bootstrap-css">
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js" integrity="sha512-asxKqQghC1oBShyhiBwA+YgotaSYKxGP1rcSYTDrB0U6DxwlJjU59B67U8+5/++uFjcuVM8Hh5cokLjZlhm3Vg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

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

    <canvas id="kedahIcu" height="250" width="250"></canvas>

    <script>
        const dataIcu = [
            <?php echo $strIcu; ?>
        ];

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
                    data: dataIcu.map(o => o.icuCovid)
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
    </script>
</div>
</body>
</html>