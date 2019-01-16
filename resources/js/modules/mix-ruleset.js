
export default {
    methods:{
        ruleset(values = 'required', label = null){
            if(!label) label = 'The field'

            let base = {
                'required': {
                    fill : (rule, text =  false)=>{
                        if(rule = 'required') return v => !!v || label + ' is required'
                    },
                },  
                'email':{
                    fill : (rule, text =  false)=>{
                        if(rule = 'email') return v => /.+@.+/.test(v) || 'The ' + label + ' is characterless'
                    },
                },
                'max':{                        
                    fill : (rule, text =  false)=>{
                        if(rule === 'max' && text) {
                            let value = Number(text.substring(4))
                            let message = label + ' cannot be more than ' + (value) + ' characters'
                            return v => !!v && v.length <= value || message
                        }
                        else return false;
                    },
                }, 
                'min':{
                    msg  : 'must be more than 10 characters',
                    fill : (rule, text =  false)=>{
                        if(rule === 'min' && text) {
                            let value = Number(text.substring(4))
                            let message = label + ' cannot be less than ' + (value) + ' characters'
                            return v => !!v && v.length >= value || message
                        }
                        else return false;
                    },
                }, 
                'regex':{
                    msg  : 'is must be valid',
                    fill : (rule, text =  false)=>{
                        if(rule === 'regex' && text) {
                            let regex = new RegExp("(?!(?:[^<]+>|[^>]+<\/a>))\b(" + text.substring(6) + ")\b", "is")
                            return v => regex.test(v) || label + ' is characters failed!'
                        }
                        else return false;
                    },
                },
            };
            let rules = [];
            
            let loop = values.split('|')
            for (let i = 0; i < loop.length; i++) {
                // console.log('================')
                // console.log('rule : ', loop[i])

                let rule = loop[i].split(':')
                if(rule.length >1)
                {
                    if(base[rule[0]] && base[rule[0]].fill(rule[0], loop[i]) !== false){
                        rules.push(base[rule[0]].fill(rule[0], loop[i]))
                    } 
                    else console.warn('WARN: Validator -> '+ rule[0])
                }
                else {
                    if(base[rule[0]] && base[rule[0]].fill(rule[0])) {
                        rules.push(base[rule[0]].fill(rule[0]))
                    } 
                    else console.warn('valid "'+ rule[0] +'" is failed!')
                }
                
            }
            return rules
        },
    }
}