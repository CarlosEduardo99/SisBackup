// ADICIONAR E REMOVER DIRETÓRIOS NO JOB
$(document).ready(function(){
	var maxField = 10; //Input fields increment limitation
	var addButton = $('.add_button'); //Add button selector
	var wrapper = $('.directory_wrapper'); //Input field wrapper
	var fieldHTML= `
		<div class="input-group mb-3">
			<input type="text" class="form-control" placeholder="/caminho/do/diretorio/..." name="directory[]" value="">
			<a href="javascript:void(0);" class="remove_button" title="Remover diretório">
				<button class="btn btn-outline-danger" type="button" id="button-addon2">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-folder-minus" viewBox="0 0 16 16">
						<path d="m.5 3 .04.87a1.99 1.99 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2zm5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19c-.24 0-.47.042-.683.12L1.5 2.98a1 1 0 0 1 1-.98h3.672z"/>
						<path d="M11 11.5a.5.5 0 0 1 .5-.5h4a.5.5 0 1 1 0 1h-4a.5.5 0 0 1-.5-.5z"/>
					</svg>
				</button>
			</a>
		</div>`; //New input field html
	var x = 1; //Initial field counter is 1

	// Once add button is clicked
	$(addButton).click(function(){
		// Check maximum numberof input fields
		if(x < maxField){
			x++; //Increase field counter
			$(wrapper).append(fieldHTML); //Add field html
		}else{
			alert('Número máximo de 10 campos permitidos atingido. ');
		}
	});

	// Once remove button is clicked
	$(wrapper).on('click', '.remove_button', function(e){
		e.preventDefault();
		$(this).parent('div').remove(); //Remove field html
		x--; //Decrease field counter
	});
});

//MOSTRA PÁGINA ATIVA
$(document).ready(function(){
	var pathName = location.pathname;
	$('.nav-link').removeClass("active");
	$('a[href=".'+pathName+'"]').addClass("active");
});

// EDITAR A RECORRÊNCIA DOS BACKUPS
$(document).ready(function (){
	var recurrenceSelect = $('#recurrence');
	var wrapper = $('.jobDate');

	$(recurrenceSelect).on('change', function () {
		var selectVal = $('#recurrence option:selected').val();

		wrapper.removeClass('d-none');
		
		switch(selectVal) {
			case '1':
				fieldHTML = `
					<label for="jobProgram" class="col-sm-4 col-form-label">Programação</label>
					<div class="col-sm-8">
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox0" name="jobProgram[days][]" value="0">
							<label for="checkbox1" class="form-check-label">Dom</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox1" name="jobProgram[days][]" value="1">
							<label for="checkbox1" class="form-check-label">Seg</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox2" name="jobProgram[days][]" value="2">
							<label for="checkbox1" class="form-check-label">Ter</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox3" name="jobProgram[days][]" value="3">
							<label for="checkbox1" class="form-check-label">Qua</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox4" name="jobProgram[days][]" value="4">
							<label for="checkbox1" class="form-check-label">Qui</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox5" name="jobProgram[days][]" value="5">
							<label for="checkbox1" class="form-check-label">Sex</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="checkbox" class="form-check-input" id="checkbox6" name="jobProgram[days][]" value="6">
							<label for="checkbox1" class="form-check-label">Sáb</label>
						</div>
					</div>`;

				wrapper.children('label').remove();
				wrapper.children('div').remove()
				wrapper.append(fieldHTML);
				break;
			case '2':
				fieldHTML = `
					<label for="jobProgram" class="col-sm-4 col-form-label">Selecione o dia</label>
					<div class="col-sm-8">
						<select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[weekly]">
							<option>Selecione...</option>
							<option value="0">Domingo</option>
							<option value="1">Segunda</option>
							<option value="2">Terça</option>
							<option value="3">Quarta</option>
							<option value="4">Quinta</option>
							<option value="5">Sexta</option>
							<option value="6">Sábado</option>
						</select>
					</div>`;

				wrapper.children('label').remove();
				wrapper.children('div').remove()
				wrapper.append(fieldHTML);
				break;
			case '3':
				fieldHTML = `
					<label for="jobProgram" class="col-sm-4 col-form-label">Selecione o dia</label>
					<div class="col-sm-8">
						<select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[monthly]">
						<option value="0">Selecione...</option>`;
				for (let i = 1; i <= 31; i++) {
					fieldHTML += '<option value="' + i + '">' + i + '</option>'
				}

				fieldHTML +=`</select>
				</div>`;
				
				wrapper.children('label').remove();
				wrapper.children('div').remove();
				wrapper.append(fieldHTML);
				break;
			case '4':
				fieldHTML = `
					<label for="jobProgram" class="col-sm-4 col-form-label">Selecione</label>
					<div class="col-sm-4">
						<select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[semi-annual][day]">
						<option value="0">Dia...</option>`;
				for (let i = 1; i <= 31; i++) {
					fieldHTML += '<option value="' + i + '">' + i + '</option>'
				}

				fieldHTML +=`
						</select>
					</div>
					<div class="col-sm-4">
						<select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[semi-annual][month]">
							<option value="">Mês...</option>
							<option value="jan">Janeiro</option>
							<option value="feb">Fevereiro</option>
							<option value="mar">Março</option>
							<option value="apr">Abril</option>
							<option value="may">Maio</option>
							<option value="jun">Junho</option>
							<option value="jul">Julho</option>
							<option value="aug">Agosto</option>
							<option value="sep">Setembro</option>
							<option value="oct">Outubro</option>
							<option value="nov">Novembro</option>
							<option value="dec">Dezembro</option>
						</select>
					</div>`;
				
				wrapper.children('label').remove();
				wrapper.children('div').remove();
				wrapper.append(fieldHTML);
				break;

				case '5':
					fieldHTML = `
						<label for="jobProgram" class="col-sm-4 col-form-label">Selecione</label>
						<div class="col-sm-4">
							<select id="recurrence" class="form-select" aria-label="Recorrência do Backup" name="jobProgram[annually][day]">
							<option value="0">Dia...</option>`;
					for (let i = 1; i <= 31; i++) {
						fieldHTML += '<option value="' + i + '">' + i + '</option>'
					}
	
					fieldHTML +=`
							</select>
						</div>
						<div class="col-sm-4">
							<select class="form-select" aria-label="Recorrência do Backup" name="jobProgram[annually][month]">
								<option value="">Mês...</option>
								<option value="jan">Janeiro</option>
								<option value="feb">Fevereiro</option>
								<option value="mar">Março</option>
								<option value="apr">Abril</option>
								<option value="may">Maio</option>
								<option value="jun">Junho</option>
								<option value="jul">Julho</option>
								<option value="aug">Agosto</option>
								<option value="sep">Setembro</option>
								<option value="oct">Outubro</option>
								<option value="nov">Novembro</option>
								<option value="dec">Dezembro</option>
							</select>
						</div>`;
					
					wrapper.children('label').remove();
					wrapper.children('div').remove();
					wrapper.append(fieldHTML);
					break;

		}
	});
});

//LIMPAR FORMULÁRIO
function resetForm($formID) {
    document.getElementById($formID).reset();
}

//TOGGLE PASSWORD

//EDITAR NOME DO BANCO DE DADOS PARA BACKUP
$(document).ready(function () {
	var hasDB = $('#hasDB');

	$(hasDB).on('change', function () {
		var selectVal = $('#hasDB option:selected').val();
		var wrapper = $('.dbNames');

		fieldHTML = `
			<label for="dbNames" class="col-sm-5 col-form-label">Nome(s) do(s) Banco(s)</label>
				<div class="col-sm-7">
					<input type="text" class="form-control" id="dbNames" name="dbNames" placeholder="Nomes separados por espaço">
				</div>`;

		if (selectVal == 'True') {
			wrapper.removeClass('d-none');
			wrapper.append(fieldHTML)
		}else {
			wrapper.children('label').remove();
			wrapper.children('div').remove();
			wrapper.addClass('d-none')
		}
	});
});