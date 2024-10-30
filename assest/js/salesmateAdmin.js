(function ( $ ) {
  "use strict";
  $(function () {
    $(document).ready(()=>{
       $('.invalidCred:eq(1)').remove();
       if($('#dealsalesmate_pipeline').length>0){

       $(document).on('change','#dealsalesmate_pipeline', function() {
          var options="";
          var index_pipeline=$(this).find('option:selected').data('index');
	console.log(index_pipeline);
          $('#dealsalesmate_stage').find('option').css('display','none');
            $('#dealsalesmate_stage').find('option[data-parentindex="'+index_pipeline+'"]').eq(0).attr('selected','selected').css('display','block');
            $('#dealsalesmate_stage').find('option[data-parentindex="'+index_pipeline+'"]').css('display','block');
       });
     }
       $(document).on('change','.dealenable',function(){
          if($(this).is(':checked')){
              $('.dealSection').css('display','block');
              $('.medatory').attr('required','required');
          }else {
              $('.dealSection').css('display','none');
              $('.medatory').removeAttr('required');
          }
       });
       // alert(myAjax.ajaxurl);
       $(document).on('click','.retry',function(){
         var data = {
            action: 'process_reservation',
            myID: $(this).data('index')
          };
          var ele = $(this);
          ele.attr('disabled','disabled');
          $.ajax(
          {
              type: "post",
              dataType: "json",
              url: myAjax.ajaxurl,
              data: data,
              success: function(msg){
                  if(msg==1){
                    alert('Record has been successfully created');
                  }else if (msg==2) {
                    alert('Your retry is failure because of record has not been created, Please make change in your salesmate fields type');
                  }else {
                    alert('Something went wrong!');
                  }
              },
              complete:function(){
                ele.attr('disabled','disabled');
                window.location.reload();
              }
          });
        });

        // Remove log
        $(document).on('click','.remove',function(){
          var data = {
             action: 'remove_log',
             myID: $(this).data('index')
           };
           var ele = $(this);
           ele.attr('disabled','disabled');
           $.ajax({

               type: "post",
               dataType: "json",
               url: myAjax.ajaxurl,
               data: data,
               success: function(msg){
                   if(msg==1){
                      alert('Record successfully removed');
                   }
               },
               complete:function(){
                 ele.attr('disabled','disabled');
                 window.location.reload();
               }
           });
        });
    });
  });

}(jQuery));
