import moment from 'moment'

export default {
    mounted(){
       // Code ..
    },
    methods:{
        moment(part1=null, part2= null, part3 = null){
            return moment(part1, part2, part3)
        },
        formatNumberAmount(number, decimals, dec_point, thousands_sep){
            if (number == 0) return '-';

            if(number < 0) return String('('+ this.formatNumber(number, decimals, dec_point, thousands_sep, true) +')');

            return this.formatNumber(number, decimals, dec_point, thousands_sep)
        },
        formatNumberText(number, decimals, dec_point, thousands_sep){
            if (number == 0){
                return '-'
            }
            return this.formatNumber(number, decimals, dec_point, thousands_sep)
        },
        formatNumber(number, decimals, dec_point, thousands_sep, is_abs = false) {
            var settings ={
                decimals: 2, 
                dec_point: ',', 
                thousands_sep: '.'
            }

            if(is_abs) number = Math.abs(number);

            var n = !isFinite(+number) ? 0 : +number, 
                prec = !isFinite(+decimals) ? Math.abs(settings.decimals) : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? settings.thousands_sep : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? settings.dec_point : dec_point,
                toFixedFix = function (n, prec) {
                    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                    var k = Math.pow(10, prec);
                    return Math.round(n * k) / k;
                },
                s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        },
        formatDate(date, to ='DD/MM/YYYY', from='YYYY-MM-DD') {
            if(date){
                return moment(date, from).format(to)
            }
        },
        parseDate(date, to = 'YYYY-MM-DD', from = 'DD/MM/YYYY'){
            if(date){
                return moment(date, from).format(to)
            }
        },
        isAvailable(value, center=false){
            if(value) return value;
            else{
                return 'N/A';
            }
        },
        setLabelText(text){
            if(text !== undefined && text !== null){
                text = text.replace(/_/gi, " ");
                text = text.charAt(0).toUpperCase() + text.slice(1)
            }

            return text
        },
        
        setLocation(link){
            window.location.href = link
        }
    }
}