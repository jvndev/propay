<script>
    let createPersonOption = (person) => 
        $('<option/>').attr('value', person.id)
            .text(`${person.lastName}, ${person.firstName} (${person.idNumber})`)
            .attr('onclick', 'getPerson('+person.id+');');

    $(() => {
        clearControls();

        getCollectionOf(
            'interests',
            (response) => {
                let interests = JSON.parse(response).data;
                let ddl = $('<select/>').attr('multiple', true).attr('id', 'interests');
                let options = interests.map(
                        (interest) => $('<option/>').text(interest.interest)
                                                    .attr('value', interest.id)
                    );

                ddl.append(options);
                $('#divInterests').append(ddl);
            }
        );

        getCollectionOf(
            'languages',
            (response) => {
                let languages = JSON.parse(response).data;
                let ddl = $('<select/>').attr('id', 'languages');
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
                $('#divLanguages').append(ddl);
            }
        );

        loadPersons();
    });

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

                ddl = ddl.attr('size', Math.min(10, options.length + 1));
                
                $('#divPersons').append(ddl);
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

    function createPerson() {
        $.ajax({
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

                    sendMail(parsed.data.id);
                    clearControls();
                    loadPersons();
                } else {
                    console.log(parsed.error);
                }
            },
            error: (response) => {
                console.log(response);
            },
        });
        
        return false;
    }

    function updatePerson() {
        $.ajax({
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
                    console.log('Person updated');

                    loadPersons();
                } else {
                    console.log(parsed.error);
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
                    console.log(parsed.message);
                    clearControls();
                    loadPersons();
                } else {
                    console.log(parsed.error);
                }
            },
            error: (response) => {
                console.log(response);
            },
        });
        
        return false;
    }

</script>

<div id='divPersons'>
    <select id='persons'></select>
</div>

<div id='divPersonCreateEdit'>
    <form method="POST">
        <div>Language</div><div id="divLanguages"></div>
        <div>Interests</div><div id="divInterests"></div>
        <div>First Name</div><div><input id='first_name' /></div>
        <div>Last Name</div><div><input id='last_name' /></div>
        <div>Cell Number</div><div><input id='cell_number' /></div>
        <div>ID Number</div><div><input id='id_number' /></div>
        <div>Email Address</div><div><input id='email' /></div>
        <button id='create' onclick="return createPerson();">
            Create
        </button>
        <button id='new' onclick="clearControls(); return false;">
            New
        </button>
        <button id='update' onclick="return updatePerson();">
            Update
        </button>
        <button id='delete' onclick="return deletePerson();">
            Delete
        </button>
    </form>
</div>