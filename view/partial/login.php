<script>
    $(() => {
        $('#divLoginMsg').text('').hide();
    });

    function login() {
        $('#divLoginMsg').text('').hide();
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
                    $('#frmLogin').submit();
                } else {
                    $('#divLoginMsg').text("Login Failed").show();
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
    <div class='form-group'>
        <label for="username">Username</label>
        <input class='form-control' type="text" id="username" placeholder="Username"></input>
    </div>
    <div class='form-group'>
        <label for="password">Password</label>
        <input class='form-control' type="password" id="password" placeholder="Password"></input>
    </div>
    <button class='btn btn-secondary 'onclick="return login();">Login</button>
    <div class='row'>
        <div id='divLoginMsg' onclick="$(this).hide();" class='col alert alert-danger'></div>
    </div>
</form>