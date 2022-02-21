<script>
    let createPersonOption = (person) => 
        $('<option/>').attr('value', person.id)
            .text(`${person.lastName}, ${person.firstName} (${person.idNumber})`)
            .attr('onclick', 'getPerson('+person.id+');');

    $(() => {
        clearControls();
        clearValidation();

        getCollectionOf(
            'interests',
            (response) => {
                let interests = JSON.parse(response).data;
                let ddl = $('#interests');
                let options = interests.map(
                        (interest) => $('<option/>').text(interest.interest)
                                                    .attr('value', interest.id)
                    );

                ddl.append(options);
            }
        );

        getCollectionOf(
            'languages',
            (response) => {
                let languages = JSON.parse(response).data;
                let ddl = $('#languages');
                let options = languages.map(
                        (language) => $('<option/>').text(language.language)
                                                    .attr('value', language.id)
                    );
                options.unshift(
                    $('<option/>').text('Please select')
                        .attr('disabled', true)
                        .attr('selected', true)
                        .attr('value', '0')
                );

                ddl.append(options);
            }
        );

        loadPersons();
    });

    function idNumberChanged() {
        let id = $('#id_number').val();

        if (re = /^(\d{6})/.exec(id)) {
            let ymd = /(\d{2})(\d{2})(\d{2})/.exec(re[0]);

            $('#day').val(ymd[3]);
            $('#month').val(ymd[2]);
            let y = (new Date().getFullYear()+"").substr(2) >= ymd[1]
                ? "20" + ymd[1] 
                : "19" + ymd[1];
            $('#year').val(y);
        }
    }

    function alertMsg(type, msg) {
        let popup = $('#divMsg');

        popup.removeClass('alert-danger')
            .removeClass('alert-success')
            .addClass(type)
            .text(msg)
            .show();
    }

    function loadPersons() {
        getCollectionOf(
            'persons',
            (response) => {
                let persons = JSON.parse(response).data;
                let options = [];
                let ddl = $('#persons');
                    
                $('#persons').empty();

                if (!persons.length) {
                    options.push(
                        $('<option/>').attr('value', '0')
                            .attr('disabled', true)
                            .text('No data')
                    );
                } else {
                    options = persons.map(createPersonOption)
                }

                ddl.append(options);
            }
        );
    }

    function clearControls() {
        $('#first_name').val('');
        $('#last_name').val('');
        $('#id_number').val('').removeAttr('readonly');
        $('#cell_number').val('');
        $('#email').val('');
        $('#languages').val(0);
        $('#interests').val(0);

        $('#create').show();
        $('#new').hide();
        $('#update').hide();
        $('#delete').hide();
    }

    function sendMail(person_id) {
        $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: 'sendMail',
                parms: {
                    person_id: person_id,
                }
            },
            method: 'POST',
            success: (response) => {
                console.log(response);
            },
            error: (response) => {
                console.log(response);
            },
        });
    }

    function getCollectionOf(model, callback) {
        $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: `get${model}`,
            },
            method: 'POST',
            success: callback,
            error: (response) => {
                console.log(response);
            },
        });
    }

    function getPerson(person_id) {
        $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: 'getPerson',
                parms: {
                    id: person_id,
                },
            },
            method: 'POST',
            success: (response) => {
                let person = JSON.parse(response).data;

                $('#first_name').val(person.firstName);
                $('#last_name').val(person.lastName);
                $('#id_number').val(person.idNumber).attr('readonly', true);
                idNumberChanged();
                $('#cell_number').val(person.cellNumber);
                $('#email').val(person.email);
                $('#languages').val(person.language.id);
                $('#interests').val(person.interests.map(
                    interest => interest.id
                ));

                $('#create').hide();
                $('#new').show();
                $('#update').show();
                $('#delete').show();
            },
            error: (response) => {
                console.log(response);
            },
        });
        
        return false;
    }

    function clearValidation() {
        $('div[id^="err"]').each(function () {
            $(this).hide();
        });
    }

    function validate() {
        clearValidation();

        return [
            ['languages', v => !!v],
            ['interests', v => v.length],
            ['first_name', v => !!v],
            ['last_name', v => !!v],
            ['cell_number', v => !!v && /^\d{10}$/.exec(v)],
            ['id_number', v => !!v && /^\d{13}$/.exec(v)],
            ['email', v => !!v && /^\w+@\w+(\.\w+)+$/.exec(v)],
        ].map(arr => {
            let ctrl = arr[0];
            let validated = arr[1]($('#'+ctrl).val());

            if (!validated) {
                $('#err_'+ctrl).show();
            } else {
                $('#err_'+ctrl).hide();
            }

            return validated;
        }).reduce((p, c) => p && c, true);
    }

    function createPerson() {
        validate() && $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: `createPerson`,
                parms: {
                    first_name: $('#first_name').val(),
                    last_name: $('#last_name').val(),
                    id_number: $('#id_number').val(),
                    cell_number: $('#cell_number').val(),
                    email: $('#email').val(),
                    language: $('#languages').val(),
                    interests: $('#interests').val(),
                }
            },
            method: 'POST',
            success: (response) => {
                let parsed = JSON.parse(response);

                if (parsed.data) {
                    $('#persons').append(createPersonOption(parsed.data));

                    clearControls();
                    loadPersons();

                    alertMsg('alert-success', 'Created');

                    sendMail(parsed.data.id);
                } else {
                    alertMsg('alert-danger', parsed.error);
                }
            },
            error: (response) => {
                console.log(response);
            },
        });
        
        return false;
    }

    function updatePerson() {
        validate() && $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: `updatePerson`,
                parms: {
                    first_name: $('#first_name').val(),
                    last_name: $('#last_name').val(),
                    id_number: $('#id_number').val(),
                    cell_number: $('#cell_number').val(),
                    email: $('#email').val(),
                    language: $('#languages').val(),
                    interests: $('#interests').val(),
                }
            },
            method: 'POST',
            success: (response) => {
                let parsed = JSON.parse(response);

                if (parsed.data) {
                    alertMsg('alert-success', 'Updated');

                    loadPersons();
                } else {
                    alertMsg('alert-danger', parsed.error);
                }
            },
            error: (response) => {
                console.log(response);
            },
        });
        
        return false;
    }

    function deletePerson() {
        $.ajax({
            accepts: 'application/json',
            url: './src/ajax.php',
            data: {
                fn: 'deletePerson',
                parms: {
                    id: $('#persons').val(),
                }
            },
            method: 'POST',
            success: (response) => {
                let parsed = JSON.parse(response);

                if (parsed.message) {
                    alertMsg('alert-success', parsed.message)
                    clearControls();
                    loadPersons();
                } else {
                    alertMsg('alert-success', parsed.error)
                }
            },
            error: (response) => {
                console.log(response);
            },
        });
        
        return false;
    }

</script>
<div class='row'>
    <div id='divMsg' onclick="$(this).hide();" class='col alert'></div>
</div>
<div class='row'>
    <div class='col-md-4'>
        <form>
            <div class='form-group'>
                <label for="persons">Existing Persons</label>
                <select id='persons' size="20" class='form-control'></select>
            </div>
        </form>
    </div>
    <div class='col-md-8'>
        <form method="POST">
            <div class='form-group'>
                <label for="languages">Language</label>
                <select id="languages" class='form-control'></select>
                <div id="err_languages" class="alert-danger">Please select a language</div>
            </div>
            <div class='form-group'>
                <label for="interests">Interests (hold "Ctrl" to select multiple)</label>
                <select multiple id="interests" class='form-control'></select>
                <div id="err_interests" class="alert-danger">Please select one or more interests</div>
            </div>
            <div class='form-group'>
                <label for="first_name">First Name</label>
                <input id='first_name' placeholder="First Name" class='form-control'></input>
                <div id="err_first_name" class="alert-danger">Invalid first name</div>
            </div>
            <div class='form-group'>
                <label for="last_name">Last Name</label>
                <input id='last_name' placeholder="Last Name" class='form-control'></input>
                <div id="err_last_name" class="alert-danger">Invalid last name</div>
            </div>
            <div class='form-group'>
                <label for="cell_number">Cell Number</label>
                <input id='cell_number' placeholder="Cell Number" class='form-control'></input>
                <div id="err_cell_number" class="alert-danger">Invalid cell number</div>
            </div>
            <div class='form-group'>
                <label for="id_number">ID Number</label>
                <input id='id_number' placeholder="ID Number" class='form-control' onkeyup="idNumberChanged();"></input>
                <div id="err_id_number" class="alert-danger">Invalid ID number</div>
            </div>
            <div class='form-group form-inline'>
                <label for="day">Day&nbsp;</label>
                <input id='day' class='form-control' style="width: 4em;" readonly></input>
                <label for="month">&nbsp;Month&nbsp;</label>
                <input id='month' class='form-control' style="width: 4em;" readonly></input>
                <label for="year">&nbsp;Year&nbsp;</label>
                <input id='year' class='form-control' style="width: 8em;" readonly></input>
            </div>
            <div class='form-group'>
                <label for="email">Email Address</label>
                <input type='email' id='email' placeholder="Email Address" class='form-control'></input>
                <div id="err_email" class="alert-danger">Invalid email address</div>
            </div>

            <button id='create' onclick="return createPerson();" class='btn btn-secondary'>
                Create
            </button>
            <button id='new' onclick="clearControls(); return false;" class='btn btn-secondary'>
                New
            </button>
            <button id='update' onclick="return updatePerson();" class='btn btn-secondary'>
                Update
            </button>
            <button id='delete' onclick="return deletePerson();" class='btn btn-secondary'>
                Delete
            </button>
        </form>
    </div>
</div>