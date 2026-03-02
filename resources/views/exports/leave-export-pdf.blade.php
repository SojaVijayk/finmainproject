{{--  <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
</head>  --}}
<style>
  @page {
size: 297mm 210mm;
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
                                <h4 class="text-center head">Leave Register</h4>

                        </div>

                    </td>

                </tr>
            </table>

        </div>
    <hr>


    <div>


            <table border="1" width="100%">
                <thead>

                  <th>Name</th>
                  <th>Leave Type</th>
                  <th>Date</th>
                  <th>Leave Day Type</th>

                  <th>Requested at</th>
                  <th>status</th>
                  <th>action By</th>
                  <th>action at</th>
                  <th>Remark</th>
                </thead>
                <tbody>

                  @foreach ($list as $item)

                  <tr style="border-bottom:1px solid black;">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->leave_type }}</td>
                    <td>{{ $item->date }}</td>
                    <td>{{ ($item->leave_day_type == 1 ? "Full Day" : ($item->leave_day_type == 2 ? "FN" : "AN")) }}</td>

                    <td>{{ $item->requested_at }}</td>
                    <td>{{ ($item->status == 0 ? 'Pending' : ($item->status == 1 ? 'Approved' : 'Rejected'))}}</td>
                    <td>{{ $item->action_by_name }}</td>
                    <td>{{ $item->action_at }}</td>
                    <td>{{ $item->remark }}</td>

                  </tr>

              @endforeach

                </tbody>

            </table>
        </div>




    </div>
