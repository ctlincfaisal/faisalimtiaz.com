<!DOCTYPE html>
<html>
<head>
  <title>Email Confirmation</title>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
</head>
<body>
  <h1>Email Confirmation</h1>
  <div id="message">Email address has ben confirmed...</div>

  <script>
    const supabaseUrl = 'https://nubyenmpdyxesfmxnrte.supabase.co';
    const supabaseAnonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im51Ynllbm1wZHl4ZXNmbXhucnRlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM2MDE3OTQsImV4cCI6MjA1OTE3Nzc5NH0.Vrc2v73dYfMn_m-2bpDNRIWboFe-jA_V1wSet_hrN3Q';

    const supabase = supabase.createClient(supabaseUrl, supabaseAnonKey);

    // ✅ Define the function AFTER Supabase is initialized
    async function handleEmailConfirmation() {
      const fragment = window.location.hash.substring(1);
      const params = new URLSearchParams(fragment);
      const accessToken = params.get('access_token');
      const refreshToken = params.get('refresh_token');

      if (accessToken && refreshToken) {
        try {
          const { data, error } = await supabase.auth.setSession({
            access_token: accessToken,
            refresh_token: refreshToken,
          });
          
          console.log('data', data);

          if (error) {
            document.getElementById('message').innerText = '❌ Error: ' + error.message;
            return;
          }

          document.getElementById('message').innerText = '✅ Email confirmed! You can now log in.';
        } catch (err) {
          document.getElementById('message').innerText = '❌ Unexpected error: ' + err.message;
        }
      } else {
        document.getElementById('message').innerText = '❌ Invalid or expired link.';
      }
    }

    // ✅ Call it ONLY AFTER everything above is defined
    handleEmailConfirmation();
  </script>
</body>
</html>
