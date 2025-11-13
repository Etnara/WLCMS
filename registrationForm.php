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
            <p class="mb-2">The following information will help us contact and identify you within our system .</p>
	    <div class="blue-div"></div>

            <label for="first_name"><em>* </em>First Name</label>
            <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">

            <label for="last_name"><em>* </em>Last Name</label>
            <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">

            <label for="email"><em>* </em>E-mail</label>
            <input type="email" id="email" name="email" required placeholder="Enter your e-mail address">

            <label for="phone"><em>* </em>Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

             <label for="organization">Organization</label>
             <input type="text" id="organization" name="organization" placeholder="Enter your organization's name">

        </fieldset>

        <fieldset class="section-box mb-4">
          <h3 class="mb-2"> Availability</h3>
          <p class="mb-2">Please select your available months.</p>
          <div class="blue-div"></div>

          <div class="main-content-box border-2 mb-0 shadow-xs w-full p-4">
              <p class="sub-text" style="text-align: center;">
                Coffee Talks are on the second Tuesday of each month.
              </p>
              <p style="text-align: center;">
                By selecting the month(s) below, you are not guaranteed to be speaking for that month.
              </p>
          </div>
          <style>
            .month-grid {
              display: grid;
              grid-template-columns: repeat(4, 1fr); /* 4 equal columns */
              gap: 10px 1px; /* vertical and horizontal spacing */
              max-width: 700px; /* optional: keeps it from stretching too wide */
            }

            .month-grid label {
              display: flex;
              align-items: center;
              justify-content: flex-start;
              gap: 4px; /* reduce spacing between checkbox and text */
              margin: 0; /* remove default label margin */
              padding: 2px 0; /* small vertical breathing room */
              cursor: pointer;
              font-size: 16px;
            }
            .month-grid input[type="checkbox"] {
              margin: 0;
              padding: 0;
              appearance: none;        /* removes Safari default spacing */
              -webkit-appearance: none;
              width: 18px;
              height: 18px;
              border: 2px solid #666;
              border-radius: 4px;
              position: relative;
            }

            .month-grid input[type="checkbox"]:checked::after {
              content: "✓";
              position: absolute;
              left: 1px;
              top: -3px;
              font-size: 16px;
              color: #2a7;
            }

            .month-grid span {
              line-height: 1; /* aligns text perfectly with checkbox */
            }
          </style>
          <!--
          <h3 class="mb-2">Select the months you're available:</h3>
          -->
          <br/>
          <div class="month-grid">
            <label><input type="checkbox" name="months[]" value="January"><span>January</span></label>
            <label><input type="checkbox" name="months[]" value="February"><span>February</span></label>
            <label><input type="checkbox" name="months[]" value="March"><span>March</span></label>
            <label><input type="checkbox" name="months[]" value="April"><span>April</span></label>
            <label><input type="checkbox" name="months[]" value="May"><span>May</span></label>
            <label><input type="checkbox" name="months[]" value="June"><span>June</span></label>
            <label><input type="checkbox" name="months[]" value="July"><span>July</span></label>
            <label><input type="checkbox" name="months[]" value="August"><span>August</span></label>
            <label><input type="checkbox" name="months[]" value="September"><span>September</span></label>
            <label><input type="checkbox" name="months[]" value="October"><span>October</span></label>
            <label><input type="checkbox" name="months[]" value="November"><span>November</span></label>
            <label><input type="checkbox" name="months[]" value="December"><span>December</span></label>
          </div>

        </fieldset>
        <fieldset class="section-box mb-4">
            <h3 class="mb-2">Event Topic</h3>
            <p class="mb-2">Please provide information of the event topic.</p>
            <div class="blue-div"></div>
            <label for="event_topic"><em>* </em>Event Topic Summary</label>
            <textarea id="topic_summary" name="topic_summary" required placeholder="Write a brief summary of the event topic" rows="3" style="resize:vertical; width:100%; border: 2px solid #cbd5e1; border-radius: 0.375rem; padding: 0.5rem;"></textarea>
            
            
	    <div class="blue-div"></div>            
        <!-- <p class="error-block">By pressing Submit below, you are sure that all entered information is correct</p> -->
        <input type="submit" name="registration-form" value="Submit" class="blue-button">
    </form>
   </div> 
</main>
