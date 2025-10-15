<!-- Hero Section with Title -->
<header class="hero-header"> 
    <div class="center-header">
        <h1>New Volunteer Registration</h1>
    </div>
</header>

<main>
  <div class="main-content-box w-full max-w-3xl p-8 mb-8">
    <form class="signup-form" method="post">
	<div class="text-center mb-8">
          <h2 class="mb-8">Registration Form</h2>
            <div class="main-content-box border-2 mb-0 shadow-xs w-full p-4">
              <p class="sub-text">Please fill out each section of the following form if you would like to volunteer for the organization.</p>
              <p>An asterisk (<em>*</em>) indicates a required field.</p>
            </div>
	</div>
        
        <fieldset class="section-box mb-4">

            <h3 class="mt-2">Personal Information</h3>
            <p class="mb-2">The following information will help us identify you within our system.</p>
	    <div class="blue-div"></div>

            <label for="first_name"><em>* </em>First Name</label>
            <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">

            <label for="last_name"><em>* </em>Last Name</label>
            <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">

            <label for="birthdate"><em>* </em>Date of Birth</label>
            <input type="date" id="birthdate" name="birthdate" required placeholder="Choose your birthday" max="<?php echo date('Y-m-d'); ?>">
            
            <label for="street_address"><em>* </em>Street Address</label>
            <input type="text" id="street_address" name="street_address" required placeholder="Enter your street address">

            <label for="city"><em>* </em>City</label>
            <input type="text" id="city" name="city" required placeholder="Enter your city">

            <label for="state"><em>* </em>State</label>
            
            <select id="state" name="state" required>
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DE">Delaware</option>
                <option value="DC">District Of Columbia</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA" selected>Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
            </select>

            <label for="zip"><em>* </em>Zip Code</label>
            <input type="text" id="zip" name="zip" pattern="[0-9]{5}" title="5-digit zip code" required placeholder="Enter your 5-digit zip code">
        </fieldset>

        <fieldset class="section-box mb-4">
            <h3>Contact Information</h3>
            <p class="mb-2">The following information will help us determine the best way to contact you regarding event coordination.</p>
	    <div class="blue-div"></div>

            <label for="email"><em>* </em>E-mail</label>
            <input type="email" id="email" name="email" required placeholder="Enter your e-mail address">

            <label for="phone"><em>* </em>Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

            <label><em>* </em>Phone Type</label>
            <div class="radio-group">
	      <div class="radio-element">
                <input type="radio" id="phone-type-cellphone" name="phone_type" value="cellphone" required><label for="phone-type-cellphone">Cell</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-home" name="phone_type" value="home" required><label for="phone-type-home">Home</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-work" name="phone_type" value="work" required><label for="phone-type-work">Work</label>
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



            
        <p class="error-block">By pressing Submit below, you are agreeing to volunteer for the organization.</p>
        <input type="submit" name="registration-form" value="Submit" class="blue-button">
    </form>
   </div> 
</main>
