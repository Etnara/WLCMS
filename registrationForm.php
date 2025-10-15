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
            <h3>Emergency Contact</h3>
            <p class="mb-2">Please provide us with someone to contact on your behalf in case of an emergency.</p>
	    <div class="blue-div"></div>

            <label for="emergency_contact_first_name" required><em>* </em>Contact First Name</label>
            <input type="text" id="emergency_contact_first_name" name="emergency_contact_first_name" required placeholder="Enter emergency contact first name">

            <label for="emergency_contact_last_name" required><em>* </em>Contact Last Name</label>
            <input type="text" id="emergency_contact_last_name" name="emergency_contact_last_name" required placeholder="Enter emergency contact last name">

            <label for="emergency_contact_relation"><em>* </em>Contact Relation to You</label>
            <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" required placeholder="Ex. Spouse, Mother, Father, Sister, Brother, Friend">

            <label for="emergency_contact_phone"><em>* </em>Contact Phone Number</label>
            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Enter emergency contact phone number. Ex. (555) 555-5555">

            <label><em>* </em>Contact Phone Type</label>
            <div class="radio-group">
	      <div class="radio-element">
                <input type="radio" id="phone-type-cellphone" name="emergency_contact_phone_type" value="cellphone" required><label for="phone-type-cellphone">Cell</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-home" name="emergency_contact_phone_type" value="home" required><label for="phone-type-home">Home</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-work" name="emergency_contact_phone_type" value="work" required><label for="phone-type-work">Work</label>
	      </div>
            </div>
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


        <fieldset class="section-box mb-4">
            <h3>Login Credentials</h3>
            <p class="mb-2">You will use the following information to log in to the system.</p>
	    <div class="blue-div"></div>

            <label for="username"><em>* </em>Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter a username">

            <label for="password"><em>* </em>Password</label>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" required>
            <p id="password-error" class="error hidden">Password needs to be at least 8 characters long, contain at least one number, one uppercase letter, and one lowercase letter!</p>

            <label for="password-reenter"><em>* </em>Re-enter Password</label>
            <input type="password" id="password-reenter" name="password-reenter" placeholder="Re-enter password" required>
            <p id="password-match-error" class="error hidden">Passwords do not match!</p>
            
              <!-- Required by backend -->
        <input type="hidden" name="is_new_volunteer" value="1">
        <input type="hidden" name="total_hours_volunteered" value="0">
        </fieldset>
            
        <p class="error-block">By pressing Submit below, you are agreeing to volunteer for the organization.</p>
        <input type="submit" name="registration-form" value="Submit" class="blue-button">
    </form>
   </div> 
</main>
