<?php
/*
    Template Name: Page des dons
*/

get_header();
tie_breadcrumbs();

?>
<script type="text/javascript">

    function localStringToNumber(value) {
        let val = value.replace(",", "."); // En France, la , sert de separateur

        let result = Number(String(val).replace(/[^0-9.-]+/g,""));
        if (result <= 0)
            result = "";

        return result;
    }

    function submitDonationForm() {
        let don = localStringToNumber( jQuery("#input_don").val() );
        if (isNaN(don) || don <= 0) {
            alert("Montant invalide");
            return false;
        }

        let hf = jQuery("<input>").attr({
            type: 'hidden',
            id:   "montant_don",
            name: "montant_don",
            value: don
        });

        jQuery("#form_don")
            .append(hf)
            .submit();
    }

    (function (window, $, undefined) {

        function onBtnDonClick (event) {
            $("#input_don").val($("#" + event.data.btn).text());
        }

        function onBtnPersonaliserDonClick() {
            const field = $("#input_don");
            field.focus();
            field.val("");
        }

        function toLocalCurrency(value) {
            let currency = 'EUR'; // https://www.currency-iso.org/dam/downloads/lists/list_one.xml
            const options = {
                maximumFractionDigits : 2,
                currency              : currency,
                style                 : "currency",
                currencyDisplay       : "symbol"
            };

            return value ? localStringToNumber(value).toLocaleString(undefined, options) : '';
        }

        $(document).ready(function () {

            let btnList = [ 5, 25, 70, "Personnaliser le montant" ]; // Liste des boutons

            $(btnList).each(function () {
                let bt_title = this;
                let bt_id = "bt_don";
                let onclickBtnDonFct = onBtnPersonaliserDonClick;
                if (! isNaN(bt_title)) {
                    // c'est un nombre
                    bt_title = toLocalCurrency("" + bt_title);
                    bt_id += "_" + this;
                    onclickBtnDonFct = onBtnDonClick;
                }

                let bt = $("<button>").attr({
                    id: bt_id,
                    class: "bt_don"
                })
                    .on("click", { btn: bt_id }, onclickBtnDonFct)
                    .text(bt_title);

                let li = $("<li>").attr({
                    class: "li_don"
                });

                $("#ul_form").append(li).append(bt); // <li class="li_don"><button id="bt_don_5"  class="bt_don" type="button">5,00 € </button>
            });

            $("#input_don")
                .on('focus', onFocus)
                .on('blur' , onBlur);

            function onFocus(e) {
                let value = e.target.value;

                e.target.value = value ? localStringToNumber(value) : '';
            }

            function onBlur(e) {
                e.target.value = toLocalCurrency(e.target.value);
            }
        });
    })(window, jQuery);

</script>

<br>

<div class="content-wrap">
    <div class="content">
        <?php
            $url_create_donnation = get_home_url() . "/index.php/don-finalisation";

            // Affiche le contenue de la page "Don-Entete"
            $header_donation_page = get_page_by_title( 'Don-Entete', OBJECT, 'page' );
            echo apply_filters( 'the_content', $header_donation_page->post_content );
        ?>
        <br>
        <form id="form_don" name="form_don" method="POST" action="<?php echo $url_create_donnation ?>">
            <span class="span_curr">€</span>
            <label for="input_don"></label>
            <input type="text" value="5,00 €" id="input_don" name="input_don" class="input_don_value" style="color: black; font: 20px Arial" required />
        </form>

        <ul id="ul_form" style="list-style: none; padding-top: 10px; padding-bottom: 20px" ></ul>

        <button type="button" class="bt_don_submit" onclick="submitDonationForm();">Régler votre don</button>

<?php get_sidebar(); ?>
<?php get_footer(); ?>