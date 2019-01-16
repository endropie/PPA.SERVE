
export default {
    install(Vue, options) {
        // "can" directive accept string with single permission or object containing "permission", and "authorId"
        Vue.directive('can', {
            bind (el, binding, vnode, oldVnode) {
                if (binding.value instanceof Object) {
                    if (binding.value.authorId==window.Laravel.uid) {
                        return true;
                    }
                    var permission = binding.value.permission;
                } else {
                    var permission = binding.value;
                }
                if (window.Laravel.permissions.indexOf(permission)==-1) {
                    el.style.display = 'none';
                }                
            }
        });

        if(!window.Laravel) window.Laravel = {};
        Vue.prototype.$auth = {
            // If authorID id is equal to current uid permission is always granted
            uID  : (window.Laravel.uid || null),
            has_roles: (window.Laravel.roles || []),
            has_permissions: (window.Laravel.permissions || []),
            csrfToken  : (window.Laravel.csrfToken || null),
            can : function (permission, authorId = false) {
                if (this.uid == authorId) {
                    return true;
                }
                if (this.has_permissions.indexOf(permission)!==-1) {
                    return true;
                }
                return false;
            },
            permission : function (permission) {
                var validate = false
                if (permission instanceof Object) {
                    $.each(permission, function( key, value ) {
                        if (this.has_permissions.indexOf(value)!==-1) {
                            validate = true
                        }
                    });
                }
                else if (this.has_permissions.indexOf(permission)!==-1) {
                    validate = true;
                }

                return validate;
            },
            role : function (role) {
                var validate = false
                if (role instanceof Object) {
                    $.each(role, function( key, value ) {
                        if (this.has_roles.indexOf(value)!==-1) {
                            validate = true
                        }
                    });
                }
                else if (this.has_roles.indexOf(role)!==-1) {
                    validate = true
                }

                return validate;
            },
        }
    },
};

