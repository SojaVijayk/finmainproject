{{--  <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
</head>  --}}
<style>
  @page {
{{--  size: 297mm 210mm;  --}}
margin: 5mm 5mm 5mm 5mm;

}
table {
border-collapse: collapse;
}
.head{
 line-height: 0.1em;
 margin-bottom: 2px;
}


/* @font-face {
font-family: 'Rachana';
font-style: normal;
font-weight: 400;
src: url('./fonts/Rachana.ttf') format('truetype');
} */
footer {
             position: fixed;
             bottom: -60px;
             left: 0px;
             right: 0px;
             height: 60;

             /** Extra personal styles **/
             /* background-color: #03a9f4; */
             color: black;
             text-align: center;
             line-height: 35px;
         }
         .numberCircle {
border-radius: 50%;
behavior: url(PIE.htc); */
/* remove if you don't care about IE8 */
width: 15px;
height: 15px;
padding: 4px;
background: #fff;
border: 2px solid #666;
color: #666;
text-align: center;
font: 15px Arial, sans-serif;
line-height: 15px;
}

</style>

<body>
     <footer>
            {{--  <img src="./assets/img/attendanceFooter.png" width="300px" height="25px">  --}}
               CMD HRMS  Generated On {{date("d:m:Y H:i:s")}}
    </footer>
    <div style="page-break-after: never;">
        <div align="center" style="width:100%;">
            <table width="100%" align="center">
                <tr>

                    {{--  <td  width="70px" align="center" style="vertical-align: middle;"><img width="60px" height="60px" src="./assets/img/branding/cmdlogo.png"></td>
                    <td style="vertical-align: top;">
                      --}}
                      <td  width="70px" align="center" >
                        <img width="60px" height="60px" src="{{ public_path('assets/img/branding/cmlogo.png') }}">




                      <div >
                                <h4 class="text-center head">Centre for Management Development</h4>
                                <h5 class="text-center head">Thycaud, Thiruvananthapuram, Kerala 695014</h5>
                                <h4 class="text-center head">Attendance Register</h4>

                        </div>

                    </td>

                </tr>
            </table>

        </div>
    <hr>


    <div>


            <table border="1" width="100%">
                <thead>
                  <th>#</th>
                  <th>Name</th>
                  <th>Designation</th>
                  <th>Date</th>
                  <th>In</th>
                  <th>Out</th>
                </thead>
                <tbody>

                  @foreach ($attendance as $item)

                  <tr style="border-bottom:1px solid black;">
                      <td>{{ $loop->iteration}}</td>
                      <td>{{ $item->name }}</td>
                      <td>{{ $item->designation }}</td>
                      <td>{{  date("d-m-Y", strtotime($item->date)) }}  </td>

                      <td>{{ $item->InTime }}</td>
                      <td>@if($item->InTime != $item->OutTime){{ $item->OutTime }} @else No Records @endif</td>



                  </tr>

              @endforeach

                </tbody>

            </table>
        </div>




    </div>
