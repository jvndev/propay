<script>
    function login() {
        $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: 'login',
                parms: {
                    username: $('#username').val(),
                    password: $('#password').val(),
                }
            },
            method: 'POST',
            success: (response) => {
                if (JSON.parse(response).message == 'success') {
                    $('#frmLogin').submit()
                }
            },
            error: (response) => {
                console.log(response);
            },
        });

        return false;
    }
</script>

<form id='frmLogin'>
    <input type="text" id="username"></input>
    <input type="text" id="password"></input>
    <button onclick="return login();">Login</button>
</form>