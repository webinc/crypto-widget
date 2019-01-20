
document.addEventListener("DOMContentLoaded", function(event) { 
  //set it to USD by default:
  if(document.getElementById('tabDefault')){
      document.getElementById('tabDefault').click();
      insertFeatured();
  }
});

//This is for basic and customiseable tabs

function getPrices(evt, currency) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(currency).style.display = "block";
  evt.currentTarget.className += " active";
}


//for inserting the featured coin at top of table:

function insertFeatured(){
    if (document.getElementById('featuredLine')){
        var rl = document.getElementById('featuredLine');
        rlh = rl.innerHTML;
        var rw = document.getElementById('featuredRow');
        rw.innerHTML = rlh;
    }
}