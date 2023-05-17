assignUserDataToTravellerForm(event) {
        
  event.preventDefault();
  const assignButton = event.currentTarget;
  
  const piloteInput = document.querySelector('input[name*="position"][value="pilote"]');
  const passagerInput = document.querySelector('input[name*="position"][value="passager"]');
  console.info('assignButton',assignButton);
  console.info('piloteInput',piloteInput);
  console.info('passagerInput',passagerInput);
  if (piloteInput && passagerInput) {
      const passagerContainer = document.querySelector('div[data-position="passager"]') ;
      console.info('passagerContainer',passagerContainer);
      const piloteContainer = document.querySelector('div[data-position="pilote"]');
      console.info('piloteContainer',piloteContainer);
      // Get the container of the clicked button
      const targetContainer = assignButton.closest('div[data-container="js-travellers-form-container"]');
      console.info('targetContainer',targetContainer);
      // Find the target container
      const originContainer = targetContainer === piloteContainer ? passagerContainer : piloteContainer;
      console.info('originContainer',originContainer);
      if (targetContainer === passagerContainer){ console.info("Target is passagerContainer")};
      if (targetContainer === piloteContainer){ console.info("Target is piloteContainer")};
      // Move the button to the target container
      targetContainer.appendChild(assignButton);
      // Move the input values between containers
      const originInputs = originContainer.querySelectorAll('input:not([name^="traveller"][name$="[position]"])');
      console.table('originInputs',originInputs);
      /* const targetInputs = targetContainer.querySelectorAll('input:not([name^="traveller"][name$="[position]"])'); */
      originInputs.forEach(input => {
          console.info('input',input);
          const name = input.getAttribute('name');
          console.info('name',name);
          const value = input.value;
          console.info('value',value);
          const targetName = name.replace(/\[(\d+)\]/, (_, index) => {
              console.info('_', _);
              console.info('index',index);
              `[${targetContainer === passagerContainer ? index : 0}]`
          });
          console.info('targetName',targetName);
          console.info('targetContainer',targetContainer);
          const targetInput = targetContainer.querySelector(`input[name="${targetName}"]`);
          console.info('targetInput',targetInput);
          targetInput.value = value;
          input.value = '';

          // Populate piloteContainer form elements with targetInput values
          const piloteName = name.replace(/\[(\d+)\]/, (_, index) => `[${targetContainer === piloteContainer ? 0 : index}]`);
          const piloteInput = piloteContainer.querySelector(`input[name="${piloteName}"]`);
          if (piloteInput) {
              piloteInput.value = targetInput.value;
          }
        });
  }

  /* console.info(assignButton);
  const originContainer = assignButton.closest('div[data-container="js-travellers-form-container"]');
  console.info(originContainer); */
  // Find the target container
  /* const targetContainer = originContainer === piloteContainer ? passagerContainer : piloteContainer;
  const $formContainer = $(event.currentTarget).closest('[data-container="js-travellers-form-container"]');
  const piloteInput = document.querySelector('div[data-container="js-travellers-form-container"] input[name="traveller[0][position]"][value="pilote"]'); 
  const passagerInput = document.querySelector('div[data-container="js-travellers-form-container"] input[name="traveller[0][position]"][value="passager"]'); */
  /* if( piloteInput && passagerInput ) {
      const piloteContainer = piloteInput.closest('div[data-container="js-travellers-form-container"]');
      const passagerContainer = passagerInput.closest('div[data-container="js-travellers-form-container"]');
  } */

  /* console.info($formContainer);
  $formContainer.find('button').hide();
  $formContainer
      .siblings('.js-contains-button')
      .find('[data-action="js-assign-to-user"]')
      .remove(); */
  /* const travellerFormData = {
      id : $formContainer.find('[name*="id"]').val(),
      prenom : $formContainer.find('[name*="prenom"]').val(),
      nom : $formContainer.find('[name*="nom"]').val(),
      email: $formContainer.find('[name*="email"]').val(),
      telephone: $formContainer.find('[name*="telephone"]').val(),
  }; */
  /* console.info(travellerFormData);
  var parentContainer = $(this).closest('[data-container="js-travellers-form-container"]');
  let buttonElement = $(event.currentTarget.outerHTML);
  buttonElement.removeAttr('style');
  parentContainer.find('h5').after(buttonElement[0].outerHTML);
  parentContainer.find('input, select, textarea').each(function() {
      this.value = '';
  });
  $formContainer.find('[name*="nom"]').val(travellerFormData.user.nom);
  $formContainer.find('[name*="prenom"]').val(travellerFormData.user.prenom);
  $formContainer.find('[name*="email"]').val(travellerFormData.user.email);
  $formContainer.find('[name*="telephone"]').val(travellerFormData.user.telephone);
  $formContainer.find('[name*="id"]').val(travellerFormData.user.id); */
  /* (async() => {
      try {
          const response = await $.ajax({
              url: Routing.generate('ajax-assign-user'),
              type: "POST"
          });
          $('input[name^="traveller"][name$="[email]"]').each(function() {
              // Check if the value of the email input field matches the user's email address
              if ($(this).val() === response.user.email) {
                  // Get the parent container element that contains all the form fields
                  var parentContainer = $(this).closest('[data-container="js-travellers-form-container"]');
                  let buttonElement = $(event.currentTarget.outerHTML);
                  buttonElement.removeAttr('style');
                  parentContainer.find('h5').after(buttonElement[0].outerHTML);
                  // Loop through all the form fields contained within the parent container
                  parentContainer.find('input, select, textarea').each(function() {
                      // Remove the value attribute from each form field
                      //$(this).removeAttr('value');
                      this.value = '';
                  });
              }

          });
          $formContainer.find('[name*="nom"]').val(response.user.nom);
          $formContainer.find('[name*="prenom"]').val(response.user.prenom);
          $formContainer.find('[name*="email"]').val(response.user.email);
          $formContainer.find('[name*="telephone"]').val(response.user.telephone);
          $formContainer.find('[name*="id"]').val(response.user.id);

      } catch (jqXHR) {
          console.error(jqXHR);
          //this._notifyErrorToUser(jqXHR);
      }
  })(); */
}