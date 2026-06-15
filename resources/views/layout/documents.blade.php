<!DOCTYPE html>
<html lang="en">

<head>
    <!-- <meta charset="UTF-8"> -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Prescription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .prescription-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 100px;
            border-radius: 50%;
        }

        .header h1 {
            margin: 10px 0 5px;
        }

        .header p {
            margin: 0;
            color: #555;
        }

        .patient-info,
        .doctor-info,
        .prescription-info {
            margin-bottom: 20px;
        }

        .patient-info h2,
        .doctor-info h2,
        .prescription-info h2 {
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 18px;
            color: #333;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .info-table th {
            background: #f9f9f9;
            color: #333;
        }

        .prescription-items {
            margin-top: 10px;
        }

        .prescription-items th,
        .prescription-items td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="prescription-container">
        @yield('content')
    </div>
</body>

</html>