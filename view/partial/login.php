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
        <div class='row'>
            <div class='col'>
                <label for="username">Username</label>
                <input type="text" id="username" placeholder="Username"></input>
            </div>
            <div class='col'>
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Password"></input>
            </div>
            <div class='col'>
                <button onclick="return login();">Login</button>
            </div>
        </div>
    </form>