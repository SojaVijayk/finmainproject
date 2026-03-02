<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
</head>
 <style>
     {{--  @font-face {
        font-family: 'myFirstFont';
        src: url('./assets/fonts/BRUSHSCI.ttf');
    }  --}}
    @font-face {
      font-family: 'myFirstFont';
      src: url({{ storage_path("fonts/BRUSHSCI.ttf") }}) format("truetype");
  }

     @page {
   size: 297mm 210mm;
   margin: 0;
}
body{
    font-size: 1em;
    color:#202060;
    background-image: url('./assets/img/branding/certificateTemplate.png');
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    background-size: 100% 100%;
}

.cer_text{
    /* font-size: 1em; */
    line-height: 200%;
}
.cer_text h2, .cer_text h4, .cer_text h3{
    font-weight: 400;
    margin:0;
    padding: 0;
}
.nameText{
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
                              <td align="center" width="15%" valign="bottom" style="padding-bottom: 1em; " >
                                <img src="./assets/img/branding/ssk_logo.png" width="100px" height="100px" style=" padding:0px;margin:0px;">
                                 </td>
                                  <td align="center" width="15%" valign="bottom" style="padding-bottom: 1em;" >
                                    <img src="./assets/img/branding/gok_logo.png" width="100px" height="100px" style=" padding:0px;margin:0px;">
                                      </td>

                  <td align="center" width="20%" valign="bottom" style="padding-bottom: 1em; ">
                     <img src="./assets/img/branding/cmd_logo_cert.png" width="100px" height="100px" style=" padding:0px;margin:0px;">
                       </td>
              </tr>
            </table>
          </div>

                <div style="width:100%; margin:0; padding-top:13em;" class="cer_text">



                            <div align="center" >
                              <h3>This is to certify that</h3>

                                <h2 class="nameText">{{ ucwords(strtolower($user->name)) }}</h2>
                                {{--  <h4><i><strong>{{ $user->email }} </strong></i></h4>  --}}
                                <br>
                                <h3 style="padding-left: 8em;padding-right: 8em;">has attended the <strong>Leadership Training Programme 2023 - 24</strong> for the Headmasters/Senior Teachers of Lower and Upper Primary Schools in Kerala organised by <strong>Samagra Shiksha Kerala</strong> as part of the <strong>STARS</strong> programme in collaboration with <strong>Centre for Management Development</strong>, Thiruvananthapuram.</h3>

                           </div>

                </div>
                <div style="padding:0em 3em; padding-left:11em;">

                <table width="100%">
                  <tr>
                                  <td align="center" width="28%" valign="bottom" style="padding-bottom: 1em; " >
                                    <img src="./assets/img/branding/seal.png" width="100px" height="100px" style=" padding:0px;margin:0px;">
                                    <hr style="color: #d7a534">
                                     <p style=" padding:0px;margin:0px; font-weight:600;">No: {{$user->ref_no}} <br><small style="color:#737373">Generated on {{ date('Y-m-d H:i:s') }}</small></p>
                                      </td>
                                      <td align="center" width="30%" valign="bottom" style="padding-bottom: 1em;" >
                                        <img src="./assets/img/branding/medal-icon.png" width="100px" height="100px" style=" padding:0px;margin:0px;">

                                          </td>

                      <td align="center" width="28%" valign="bottom" style="padding-bottom: 1em; ">
                         <img src="./assets/img/branding/signature.png" width="110px" height="100px" style=" padding:0px;margin:0px;">
                         <hr style="color: #d7a534">
                          <p style=" padding:0px;margin:0px; font-weight:600;">CA. Dr. Binoy J. Kattadiyil DSc  <br><small style="color:#865b34">Director & Member Secretary</small></p>
                      </td>
                  </tr>
                  <tr>
                      <td colspan="4">
                          <table width="100%">
                              <tr>
                                  <td>
                                      <p style="font-size: .8em; margin:0; padding:0;">
                                      {{--  Note : This certificate is computer generated and does not require any Seal in original.  --}}

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
