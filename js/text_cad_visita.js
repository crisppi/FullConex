 // mudar linhas do relatorio 
 var text_audit = document.querySelector("#rel_visita_vis");

 function aumentarTextAudit() {
     if (text_audit.rows == "2") {
         text_audit.rows = "30"
     } else {
         text_audit.rows = "2"
     }
 }

 // mudar linhas da acoes 
 var text_acoes = document.querySelector("#acoes_int_vis");

 function aumentarTextAcoes() {
     if (text_acoes.rows == "2") {
         text_acoes.rows = "30"
     } else {
         text_acoes.rows = "2"
     }
 }


 // mudar linhas dos exames enf 
 var text_exames_enf = document.querySelector("#exames_enf");

 function aumentarTextExamesEnf() {
     if (text_exames_enf.rows == "2") {
         text_exames_enf.rows = "30"
     } else {
         text_exames_enf.rows = "2"
     }
 }

 // mudar linhas dos programacao_enf enf 
 var programacao_enf = document.querySelector("#programacao_enf");

 function aumentarTextProgEnf() {
     if (programacao_enf.rows == "2") {
         programacao_enf.rows = "30"
     } else {
         programacao_enf.rows = "2"
     }
 }

 // mudar linhas dos exames enf 
 var oportunidades_enf = document.querySelector("#oportunidades_enf");

 function aumentarTextOportEnf() {
     if (oportunidades_enf.rows == "2") {
         oportunidades_enf.rows = "30"
     } else {
         oportunidades_enf.rows = "2"
     }
 }
 $(document).ready(function() {
     // Adicione um ouvinte de mudança ao checkbox button
     $('#exibirVisita').change(function() {
         // Verifique se o checkbox button está marcado
         if ($(this).is(':checked')) {
             // Se estiver marcado, mostre a div
             $('#div-visitas').show();
             $('#textVisita').text('Ocultar visitas');

         } else {
             // Se não estiver marcado, oculte a div
             $('#div-visitas').hide();
             $('#textVisita').text('Exibir visitas anteriores');

         }
     });
 });


 // aparecer campos relatorio detalhado
 $(document).ready(function() {
     $('#div-detalhado').hide(); // Oculta o campo de texto quando a página carrega

     $('#relatorio-detalhado').change(function() {
         if ($(this).val() === 's') {
             $('#div-detalhado').show();
             $('#text-detalhado').hide();

         } else {
             $('#div-detalhado').hide();
             $('#text-detalhado').show();

         }
     });
 });

 // aparecer campo atb em uso
 $(document).ready(function() {
     $('#atb').hide(); // Oculta o campo de texto quando a página carrega

     $('#atb_enf').change(function() {
         if ($(this).val() === 's') {
             $('#atb').show();
         } else {
             $('#atb').hide();
         }
     });
 });
 // aparecer campo litros de O2
 $(document).ready(function() {
     $('#div-oxig').hide(); // Oculta o campo de texto quando a página carrega

     $('#oxig_enf').change(function() {
         if ($(this).val() === 'Cateter' || $(this).val() == 'Mascara') {
             $('#div-oxig').show();
         } else {
             $('#div-oxig').hide();
         }
     });
 });