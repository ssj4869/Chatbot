<?php
error_reporting(0);
?>
<html>
    <head>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js" ></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="styles.css"/>
	<link rel="stylesheet" type="text/css" href="chat.css"/>
	<script>
			function sendMsg() {
				var msg = $("#msg").val();
				if(msg=='') {
					alert('Enter text to be sent');
				} else {
					//$("#m_body").html("<p align='right'>"+msg+"</p>");
					$.ajax({
						url: "main.php",
						data: "msg="+msg,
						type: "POST",
						success: function(data) {
							if(data== 'OK') {
								alert("You can no longer send messages to this conversation");
							} else {
								$("#msg").val('');
								$("#m_body").html(data);
							}
						}
					});

				}
			}
			function delete_() {
				$.ajax({
					url: "main.php",
					data: "del=1",
					type: "POST",
					success: function(data) {}
				});
			}
	</script>

</head>

	<body>
  <div class="jumbotron">
    <h1>Amateras Business</h1>      
  </div>
  <div style="text-align:center; background-color: #f0f0f0;"class="jumbotron">
    <h4>Searching for products made it easier, thanks to ever changing technology</h4><br>
	<h5>Meet Goku. A virtual chat bot to help you find the right Laptop product which will match your work flow</h5><br>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
    Click here to get started
  </button>

  </div>


<div class="container">

  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-xl ">
      <div style="height: 80%" class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">You're now talking with Goku, A Virtual Assistant!</h4>
          <button type="button" id="del" onclick="return delete_();" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body" style="overflow:auto;"  id="m_body">
		<?php
		?>
        </div>
        
        <!-- Modal footer -->
				<div class="modal-footer">
				  <div class="input-group">
		  <input type="text" placeholder="Enter your message here!" id="msg" class="form-control">  
		  <div class="input-group-append">
			<button class="btn btn-dark" id='send' onclick="return sendMsg();"type="button">Send</button>
		  </div>
		</div>

        </div>
        
      </div>
    </div>
  </div>
  
</div>
</body>

</html>