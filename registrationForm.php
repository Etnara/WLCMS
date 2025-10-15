<!-- Hero Section with Title -->
<header class="hero-header"> 
    <div class="center-header">
        <h1>Interested Speaker Form</h1>
    </div>
</header>

<main>
  <div class="main-content-box w-full max-w-3xl p-8 mb-8">
    <form class="signup-form" method="post">
	<div class="text-center mb-8">
          <h2 class="mb-8">Interest Form</h2>
            <div class="main-content-box border-2 mb-0 shadow-xs w-full p-4">
              <p class="sub-text">Please fill out each section of the following form if you would be interested in speaking for the Women's Leadership Colloquium.</p>
              <p>An asterisk (<em>*</em>) indicates a required field.</p>
            </div>
	</div>
        
        <fieldset class="section-box mb-4">

            <h3 class="mt-2">Personal Information</h3>
            <p class="mb-2">The following information will help us contacct you and identify you within our system .</p>
	    <div class="blue-div"></div>

            <label for="first_name"><em>* </em>First Name</label>
            <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">

            <label for="last_name"><em>* </em>Last Name</label>
            <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">

            <label for="email"><em>* </em>E-mail</label>
            <input type="email" id="email" name="email" required placeholder="Enter your e-mail address">

            <label for="phone"><em>* </em>Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

        

        </fieldset>



        <fieldset class="section-box mb-4">
            <h3 class="mb-2">Event Topic</h3>
            <p class="mb-2">Please provide information of the event topic and summary of it.</p>
            <div class="blue-div"></div>
            <label for="event_topic"><em>* </em>Event Topic Name</label>
            <input type="text" id="event_topic" name="event_topic" required placeholder="Enter the event topic name">

            <label for="event_topic_summary"><em>* </em>Event Topic Summary</label>
            <textarea id="event_topic_summary" name="event_topic_summary" required placeholder="Write a brief summary of the event topic" rows="3" style="resize:vertical; width:100%; border: 2px solid #cbd5e1; border-radius: 0.375rem; padding: 0.5rem;"></textarea>
            
	    <div class="blue-div"></div>


        </fieldset>

        
               

                
        <script>
            

            
           

            
            

             // Event listeners for changes in volunteer/participant selection and the complete statuses
            //document.querySelectorAll('input[name="is_community_service_volunteer"]').forEach(radio => {
              //  radio.addEventListener('change', toggleTrainingSection);
            //});



            
            // Initial check on page load
            
        </script>


        
            
        <p class="error-block">By pressing Submit below, you are sure that all entered information is correct</p>
        <input type="submit" name="registration-form" value="Submit" class="blue-button">
    </form>
   </div> 
</main>
