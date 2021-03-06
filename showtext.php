<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Show text</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </head>
  <body>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-0 col-md-3"></div>
        <div id="textdiv" class="col-sm-12 col-md-6">
          <?php
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
            $time_start = microtime(true);
            require_once 'connect.php'; // connect to database
            require_once 'functions.php'; // used for addParagraphs, addLinks & colorizeWords

            $id = mysqli_real_escape_string($con, $_GET['id']);

            $result = mysqli_query($con, "SELECT text, textTitle FROM texts WHERE textID='$id'") or die(mysqli_error($con));

            $row = mysqli_fetch_assoc($result);

            echo '<h1>'.$row['textTitle'].'</h1>'; // display title

            $text = $row['text']; // display text

            $text = colorizeWords($text);
            $text = addlinks($text);
            echo addParagraphs($text); // convert /n to HTML <p>

            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start);
            echo '<b>Total Execution Time:</b> '.$execution_time.' Secs';
           ?>
           <p></p>
           <button type="button" id="btnfinished" class="btn btn-lg btn-success btn-block">Finished reading</button>
           <p></p>
        </div>
        <div class="col-sm-0 col-md-3"></div>
      </div>
    </div>



  </body>
</html>

<!-- Modal window -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Dictionary</h4>
      </div>
      <div class="modal-body" id="definitions">
        <iframe id="dicFrame" style="width:100%;" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<script>

  $(document).ready(function() {

    $(document).on("click", "a", function(){
      // show dictionary
      url = 'http://www.wordreference.com/fres/' + this.text;
      $('#dicFrame').attr('height', $(window).height()-150);
      $('#dicFrame').attr('src', url);

      // add word to "words" table
      $.ajax({
        type: "POST",
        url: "addword.php",
        data: { word: this.text },
        context: this,
        success: function(data){
          //$(this).toggleClass('word learning');
          var element = $(this);
          var word = element.text().toLowerCase();
          $('a').each(function(){
            linkword = $(this).text().toLowerCase();
            if (word == linkword) {
              // remove old underlining if it already exists
              if ($(this).parent().hasClass('word')) {
                $(this).parent().replaceWith($(this));
              }
              // add 'new' underlining
              $(this).wrap("<span class='word new'></span>");
            }
          });
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          alert("Oops! There was an error adding the word to the database.");
        }
      });
    });

    $('#btnfinished').on("click", function() {
        // build array with underlined words
        var oldwords = [];
        var word = "";
        $('.learning').each(function(){
          word = $(this).text().toLowerCase();
          if (jQuery.inArray(word, oldwords) == -1) {
            oldwords.push(word);
          }

        })

        // alert(JSON.stringify(res));

        $.ajax({
          type: "POST",
          url: "finishedreading.php",
          data: { words: oldwords },
          success: function(data) {
             //alert(data);
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert("Oops! There was an error updating the database.");
          }
        });
    });

  });

</script>
