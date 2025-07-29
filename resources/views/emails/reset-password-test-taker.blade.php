
<!DOCTYPE html>
<html>

<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <style type="text/css">
    /* CLIENT-SPECIFIC STYLES */
    body,
    table,
    td,
    a {
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    table,
    td {
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }

    img {
      -ms-interpolation-mode: bicubic;
    }

    /* RESET STYLES */
    img {
      border: 0;
      height: auto;
      line-height: 100%;
      outline: none;
      text-decoration: none;
    }

    table {
      border-collapse: collapse !important;
    }

    body {
      height: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
      width: 100% !important;
      font-family: Calibri, sans-serif;
    }

    /* iOS BLUE LINKS */
    a[x-apple-data-detectors] {
      color: inherit !important;
      text-decoration: none !important;
      font-size: inherit !important;
      font-family: inherit !important;
      font-weight: inherit !important;
      line-height: inherit !important;
    }

    /* ANDROID CENTER FIX */
    div[style*="margin: 16px 0;"] {
      margin: 0 !important;
    }
  </style>
</head>

<body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <!-- LOGO -->
  <tr>
    <td bgcolor="#F3F4F6" align="center">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
        <tr>
          <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#F3F4F6" align="center" style="padding: 0px 10px 0px 10px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
        <tr>
          <td bgcolor="#ffffff" align="center" valign="top" style="border-left: 1px solid #E7E8EB; border-right: 1px solid #E7E8EB; border-top: 1px solid #E7E8EB; padding: 48px 48px 16px 48px; border-radius: 4px 4px 0px 0px; color: #111111; text-align: left; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
            <img src="{{ asset('/images/logo.png')  }}" width="60" height="60" style="display: block; border: 0px;" />
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
        <tr>
          <td bgcolor="#ffffff" align="left" style="border-left: 1px solid #E7E8EB; border-right: 1px solid #E7E8EB; padding: 16px 48px 8px 48px; color: #393D42; font-size: 12px; font-weight: 700; line-height: 15px; letter-spacing: 0.015em;">
            <p style="margin: 0;">Your password has been reset, here is your new password!</p>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="left" style="border-left: 1px solid #E7E8EB; border-right: 1px solid #E7E8EB; padding: 8px 48px 8px 48px; color: #393D42; font-size: 12px; font-weight: 400; line-height: 15px; letter-spacing: 0.015em;">
            <table>
              <tr>
                <td>Name</td>
                <td style="padding-left: 20px; padding-right: 3px;">:</td>
                <td>{{ $mailData['name'] }}</td>
              </tr>
              <tr>
                <td>Email</td>
                <td style="padding-left: 20px; padding-right: 3px;">:</td>
                <td><b>{{ $mailData['email'] }}</b></td>
              </tr>
              <tr>
                <td>New Password</td>
                <td style="padding-left: 20px; padding-right: 3px;">:</td>
                <td><b>{{ $mailData['new-password'] }}</b></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="left" style="padding: 8px 48px 8px 48px; text-align: left; border-left: 1px solid #E7E8EB; border-right: 1px solid #E7E8EB;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td bgcolor="#ffffff" align="center">
                  <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center" style="border-radius: 3px;" bgcolor="#4f46e5"><a href="{{ url('/login-taker')  }}" target="_blank" style="font-size: 12px; text-decoration: none; color: #ffffff; padding: 8px 16px; border-radius: 4px; border: 1px solid #4f46e5; display: inline-block;">Login as Candidate</a></td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="left" style="border-left: 1px solid #E7E8EB; border-right: 1px solid #E7E8EB; padding: 8px 48px 8px 48px; color: #393D42; font-size: 12px; font-weight: 400; line-height: 15px; letter-spacing: 0.015em;">
            <p style="margin: 0;">Or via the following link <a href="{{ url('/login-taker')  }}" style="color: #184F89;">{{ url('/login-taker')  }}</a></p>
          </td>
        </tr>
        <tr>
          <td bgcolor="#ffffff" align="left" style="border-left: 1px solid #E7E8EB; border-right: 1px solid #E7E8EB; border-bottom: 1px solid #E7E8EB; padding: 8px 48px 48px 48px; border-radius: 0px 0px 4px 4px; color: #393D42; font-size: 12px; font-weight: 400; line-height: 15px; letter-spacing: 0.015em;">
            <p style="margin: 0;">Kind regards,<br><b>IONBEC</b></p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
        <tr>
          <td bgcolor="#f4f4f4" align="left" style="padding: 12px 0px 24px 0px; color: #9C9C9C; font-size: 11px; font-weight: 400; line-height: 13px; letter-spacing: 0.015em; text-align: center;"> <br>
            <p style="margin: 0;">This email is sent automatically, please do not reply to this email directly.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>

</html>
