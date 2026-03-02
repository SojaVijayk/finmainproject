<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title></title>
</head>
<style>
  {
      {
      -- @font-face {
        font-family: 'myFirstFont';
        src: url('./assets/fonts/BRUSHSCI.ttf');
      }

      --
    }
  }

  @font-face {
    font-family: 'myFirstFont';
    src: url("{{ storage_path('fonts/BRUSHSCI.ttf') }}") format("truetype");
  }

  @page {
    size: 297mm 210mm;
    margin: 0;
  }

  body {
    font-size: 1em;
    color: #202060;
    background-image: url('./assets/img/branding/certificateASEM.png');
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    background-size: 100% 100%;
  }

  .cer_text {
    /* font-size: 1em; */
    line-height: 200%;
  }

  .cer_text h2,
  .cer_text h4,
  .cer_text h3 {
    font-weight: 400;
    margin: 0;
    padding: 0;
  }

  .nameText {
    font-family: 'myFirstFont' !important;
    font-size: 2.5em !important;
    color: #A31E24;
  }
</style>

<body>
  {{-- <div style="page-break-after: always;"> --}}

    <div>

      <div style="padding:1em 3em; padding-left:11em;">

        <table width="100%">
          <tr>
            {{-- <td align="right" width="50%" valign="bottom" style="padding-bottom: 1em; ">
              <small>Ref.No: <strong>{{$user->ref_no}}</strong></small>
            </td> --}}
            {{-- <td align="center" width="50%" valign="bottom" style="padding-bottom: 1em; ">
              <img src="./assets/img/branding/ASEM-logo.png" width="150px" height="150px"
                style=" padding:0px;margin:0px;">
            </td>
            <td align="center" width="50%" valign="bottom" style="padding-bottom: 1em;">
              <img src="./assets/img/branding/CMD-logo.png" width="200px" height="200px"
                style=" padding:0px;margin:0px;">
            </td> --}}

            {{-- <td align="center" width="20%" valign="bottom" style="padding-bottom: 1em; ">
              <img src="./assets/img/branding/cmd_logo_cert.png" width="100px" height="100px"
                style=" padding:0px;margin:0px;">
            </td> --}}
          </tr>
        </table>
      </div>

      <div style="width:100%; margin:0; padding-top:18em;" class="cer_text">



        <div align="center">
          <h3>This is to certify that</h3>

          <h2 class="nameText">{{ ucwords(strtolower($user->name)) }}</h2>
          {{-- <h4><i><strong>{{ $user->email }} </strong></i></h4> --}}
          <br>
          <h3 style="padding-left: 8em;padding-right: 8em;">@if($user->designation!= '' && $user->designation!=
            NULL){{$user->designation}}, @endif
            @if($user->institution!= '' && $user->institution!= NULL){{$user->institution}},@endif has
            participated in the one day programme
            on
            <strong>“
              International Multiplier Training- Entrepreneurship Education for All ” </strong>conducted by Centre for
            Management Development in
            collaboration with ASEM Lifelong Learning South Asia centre on
            <strong>November 17, 2025 </strong> at the <strong>Centre for Management Development,
              Thiruvananthapuram.</strong>
          </h3>

        </div>

      </div>

      <div style=" position: absolute;
    bottom: 70px;
    left: 10em;
    width: 100%;
    text-align: center;
    padding: 0 2em;">

        <table width="100%">



          <tr>
            <td colspan="4">
              <table width="100%">
                <tr>
                  <td>
                    <p style="font-size: .8em; margin:0; padding:0;">
                      Ref.No: <strong>{{$user->ref_no}}</strong>
                      <small>This certificate is system generated. Issued on {{
                        $user->certificate_generated_at}}</small>

                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>


        </table>
      </div>


</body>

</html>