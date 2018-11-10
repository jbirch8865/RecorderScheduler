$(document).ready(function(){
    $.ajax({url: "isRecording.php", success: function(result){
        if(result == 'True')
		{
			$("#RecordingStatus").attr('value','Stop');
			$("#Recordbutton").attr('value','Stop Recording');

		}else
		{
			$("#Recordbutton").attr('value','Start Recording');
			$("#RecordingStatus").attr('value','Start');
			
		}
			$("#Recordbutton").prop('disabled', false);
		
    }});
});