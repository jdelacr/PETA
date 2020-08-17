
var lang = 'USD';
var pagelang = $('html')[0].lang;
var infoLink =
    '<a href="https://fixer.io/" target="_blank"style="color: #999;"><i aria-hidden="true" class="fa fa-info-circle" style="float:right;"></i></a>';

//Function to determine the output of the language in the info box 
function loadLang(lang, currency) {
    var info = '';
    switch (lang) {
        case 'en':
            info = `<p style="font-size:.8rem;">Conversion to ${currency} are based on current exchange rates to aid you in your selection. All gifts are shown in USD.</p><hr></hr><h3 id="pseudoRates"></h3>`;
            break;
        case 'es':
            info = `<p style="font-size:.8rem;">La conversión a ${currency} se basa en los tipos de cambio actuales para ayudarle en su selección. Todos los regalos se muestran en USD.</p><hr></hr><h3 id="pseudoRates"></h3>`;
            break;
        default:
            info = `<p style="font-size:.8rem;">Conversion to ${currency} are based on current exchange rates to aid you in your selection. All gifts are shown in USD.</p><hr></hr><h3 id="pseudoRates"></h3>`;
    }

    return info;
}

//jQuery for currency
$(document).ready(function () {

    //Get the cache
    $.ajax({
        type: 'get',
        url: 'cacheAPI.php',
        dataType: 'JSON',
        success: function(res){
            appendConverter(res);
        },
        //Could not get fixer.io API
        error: function (res) {
            console.error(res.responseText);
         }
    });

    function appendConverter(res){
        var node = [res];
    //Initiate appending the currency converter selector
    $('.en__field--donationAmt').before('<div class="en__field en__field--select en__field--0000 en__field--pseudo-currencyConverter"><div class="en__field__element en__field__element--select" style="width:50%"><select id="en__field_pseudo_currencyConverter" class="en__field__input en__field__input--select" name="currencyConverter" style="border: none;"></select></div><div id="pseudo_Info" style="padding: 1rem; display: none; border: 1px solid darkgray; border-radius:5px;"></div></div>');

    //If there is an error in the API, then the block is hidden
    if (node[0].success == false || node == null) {
        $('.en__field--pseudo-currencyConverter').css('display', 'none');
        if (node[0].success == false){
            console.error(node[0].error['info'] + ' https://fixer.io/');
        }
    }
    else {
            //Print out the lists of currencies
    for (const [key, value] of Object.entries(node[0].rates)) {
        if (key == lang) {
            $('select#en__field_pseudo_currencyConverter').append(
                `<option value="${key}">Preferred currency ${key}	</option>`);
        } else {
            $('select#en__field_pseudo_currencyConverter').append(
                `<option value="${key}">${key}</option>`);
        }

        console.log(`${key}: ${value}`);
    }


    $('select#en__field_pseudo_currencyConverter').change(function () {
        var str = '';

        if (this.value != lang) {
            $('div#pseudo_Info').css('display', 'block');
        } else {
            $('div#pseudo_Info').css('display', 'none');
        }


        console.log($('select#en__field_pseudo_currencyConverter').val());

        $('select#en__field_pseudo_currencyConverter option:selected').each(
            function () {

                var selectedRate = node[0].rates[this.value];
                var currentLangRate = node[0].rates[lang];
                var selectedAmt = $('input[name="transaction.donationAmt"]:checked').val();
                var formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: this.value,
                    currencyDisplay: 'narrowSymbol'
                })

                str = $('div#pseudo_Info').html(loadLang(pagelang, this.value));

                var Calc = (selectedAmt * selectedRate) / currentLangRate;

                //Determine if the value is nothing
                if (!selectedAmt) {
                    //If the input box is empty, set the amount to 0 for default output
                    if(!$('input[name="transaction.donationAmt.other"]').val()){
                        selectedAmt = 0;
                    }
                    else{
                        //If there is a value in the input box after changing currency, get the value in the input box
                        selectedAmt = $('input[name="transaction.donationAmt.other"]').val();
                    }
                    //Calculate the selected amount
                    Calc = (selectedAmt * selectedRate) / currentLangRate;
                }

                //Default output when the dropdown has been selected
                var ConverstionRate = '$' + selectedAmt + ' ' + lang + ' = ' + formatter.format(Calc) + ' ' + this.value;
                $('h3#pseudoRates').html(ConverstionRate + infoLink);

                //Action to take when one of the buttons have been changed
                $('input[name="transaction.donationAmt"]').on('click',
                    function () {
                        var selectedAmt = $('input[name="transaction.donationAmt"]:checked').val();
                        var currency = $('select#en__field_pseudo_currencyConverter option:selected').val();


                        //Get the rate
                        var selectedRate = node[0].rates[currency];
                        var currentLangRate = node[0].rates[lang];
                        var formatter = new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: currency,
                            currencyDisplay: 'narrowSymbol'
                        });

                        //Calculate the rate
                        var Calc = (selectedAmt * selectedRate) / currentLangRate;

                        //Output text
                        var ConversionRate = '$' + selectedAmt + ' ' + lang + ' = ' + formatter.format(Calc) + ' ' + currency;

                        //Calculate the rates when inputting the value
                        $('input[name="transaction.donationAmt.other"]')
                            .keyup(
                                function () {

                                    var currency = $('select#en__field_pseudo_currencyConverter option:selected').val();
                                    var selectedRate = node[0].rates[currency];
                                    var currentLangRate = node[0].rates[lang];
                                    var formatter = new Intl.NumberFormat(
                                        'en-US', {
                                            style: 'currency',
                                            currency: currency,
                                            currencyDisplay: 'narrowSymbol'
                                        })

                                    var Calc = (this.value * selectedRate) / currentLangRate;
                                    var ConversionRate = '$' + this.value + ' ' + lang + ' = ' + formatter.format(Calc) + ' ' + currency;

                                    //Setting default value when the custom input is empty
                                    if (!this.value) {
                                        $('h3#pseudoRates').html('$0 ' + lang + ' = ' + formatter.format(Calc) + ' ' + currency + infoLink);
                                    } else {
                                        $('h3#pseudoRates').html(
                                            ConversionRate + infoLink);
                                    }
                            });

                        //Setting default value when the custom input is empty when clicking the other button
                        if (!selectedAmt) {
                            $('h3#pseudoRates').html('$0 ' + lang + ' = ' +
                                formatter.format(Calc) + ' ' +
                                currency +
                                infoLink);
                        } else {
                            $('h3#pseudoRates').html(ConversionRate +
                                infoLink);
                        }

                        //Hide block when the dropdown has been selected as US
                    });
            })
    });
    }
}
});