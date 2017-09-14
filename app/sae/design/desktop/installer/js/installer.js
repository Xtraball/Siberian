/*global
    $, console, Class
 */
var installer = {
    contain_errors: false,
    installation_url: '',
    cron_url: '',
    cron_timeout: '',
    installation_url_data: '',
    database_connection_is_set: false,
    admin_is_created: false,
    application_is_created: false,
    is_installed: false,
    insert_data: false,
    steps: {},
    plugins: [],
    current_step_id: 0,
    last_step_id: 0,
    init: function () {
        $('#sidebar').css('opacity', 1);
        setTimeout(this.nextStep.bind(this), 300);
    },
    setPlugins: function (plugins) {
        this.plugins = plugins;
        return this;
    },
    addStep: function (step) {
        this.steps[step.id] = step;
        this.last_step_id = step.id;
        return this;
    },
    nextStep: function () {
        if (this.isInstalled() && (this.current_step_id === this.last_step_id)) {
            window.location = '/backoffice';
            return this;
        }

        if (this.steps[this.current_step_id]) {
            if (!this.steps[this.current_step_id].canGoForward()) {
                return this;
            }
            this.steps[this.current_step_id].hide('next');
        }

        this.steps[++this.current_step_id].show();

        $('#sidebar li').removeClass('sidebar_hover f-bold');
        $('#sidebar li.step'+this.current_step_id).addClass('sidebar_hover f-bold');

        this.checkButtons();
    },
    previousStep: function () {
        if (!this.steps[this.current_step_id-1]) {
            return this;
        }

        this.steps[this.current_step_id].hide('prev');
        this.steps[--this.current_step_id].show();

        $('#sidebar li').removeClass('sidebar_hover f-bold');
        $('#sidebar li.step'+this.current_step_id).addClass('sidebar_hover f-bold');

        this.checkButtons();
    },
    checkButtons: function () {
        if (!this.contain_errors) {
            $('#previous_step').removeAttr('disabled').css('opacity', 1);
            $('#next_step').removeAttr('disabled').css('opacity', 1);
            if (!this.steps[this.current_step_id-1]) {
                $('#previous_step').attr('disabled', 'disabled').css('opacity', 0.5);
            }
        }
    },
    deactivateButtons: function () {
        $('#previous_step').attr('disabled', 'disabled').css('opacity', 0.5);
        $('#next_step').attr('disabled', 'disabled').css('opacity', 0.5);
        return this;
    },
    containErrors: function () {
        this.contain_errors = true;
        this.deactivateButtons();
    },
    checkDatabaseConnection: function () {
        if (!this.database_connection_is_set) {
            this.deactivateButtons();
            $('.checking_connection_status').hide();
            $('#checking_connection').fadeIn();
            $.ajax({
                url: this.check_database_connection_url,
                type: 'POST',
                data: $('#databaseForm').find('input').serializeArray(),
                dataType: 'json',
                success: function (datas) {
                    if (datas.success) {
                        $('#checking_connection').hide();
                        $('#connection_is_ok').fadeIn();
                        this.database_connection_is_set = true;
                        setTimeout(this.nextStep.bind(this), 3000);
                    }
                }.bind(this),
                error: function (datas) {
                    var error;
                    try {
                        error = $.parseJSON(datas.responseText);
                        error = error.message;
                    } catch (e) {
                        error = 'Error';
                    }
                    $('#checking_connection').hide();
                    $('#connection_has_failed').html('<i class="icon-remove"></i> ' + error).fadeIn();
                    this.checkButtons();
                }.bind(this),
                complete: function () {

                }
            });

            return false;
        }

        return true;
    },
    startInstallation: function () {
        if (!this.is_installed) {
            $('#previous_step').attr('disabled', 'disabled').css('opacity', 0.5);
            $('#next_step').attr('disabled', 'disabled').css('opacity', 0.5);

            this.installPlugin(0);
        }
    },
    installPlugin: function (key) {
        if (!this.insert_data && key >= Object.getSize(this.plugins)) {
            key = 0;
            this.insert_data = true;
            $('#label_install').html('Inserting data');
        }

        if (this.insert_data && key >= Object.getSize(this.plugins)) {
            installer.endInstallation();
        } else {
            $('#label_module').html(this.plugins[key]);
            $('#module_name').val(this.plugins[key]);
            if (!this.insert_data && key === 0) {
                $('#progressbar').css('width', '1%'); /** Start at 1% for convenience */
                $('.binding-progress').text('1%');
            }

            var url_step = (this.insert_data) ? this.installation_url_data : this.installation_url;

            $.ajax({
                url: url_step,
                type: 'POST',
                data: $('#installationForm').find('input').serializeArray(),
                dataType: 'json',
                success: function (datas) {
                    if (datas.success) {
                        var fake_key = (this.insert_data) ? (Object.getSize(this.plugins) + key) : key;
                        var width = (fake_key + 1) * (100 / Object.getSize(this.plugins)) / 2;
                        $('#progressbar').css('width', width + '%');
                        $('.binding-progress').text(Math.round(width) + '%');
                        this.installPlugin(key + 1);
                    }
                }.bind(this),
                error: function (datas) {
                    var error;
                    try {
                        error = $.parseJSON(datas.responseText);
                        error = error.message;
                    } catch (e) {
                        error = 'Error';
                    }
                },
                complete: function () {

                }
            });
        }
    },
    installCron: function () {
        $.ajax({
            url: this.cron_url,
            type: 'GET',
            dataType: 'json',
            timeout: 5000,
            async: true,
            success: function (datas) {
                console.log('[CRON] scheduler succes');
            },
            error: function (datas) {
                console.log('[CRON] scheduler error');
            },
            complete: function () {
                console.log('[CRON] request done ....');
            }
        });
    },
    endInstallation: function () {
        for (var i=1; i<this.current_step_id; i++) {
            delete this.steps[i];
        }

        $('#installation_progress').hide();
        $('#installation_ending').fadeIn();
        $.get('installer/installation/end', function () {
            this.is_installed = true;
            $('#installation_ending').hide();
            $('#installation_successful').fadeIn();
            setTimeout(this.nextStep.bind(this), 3000);
        }.bind(this));
    },
    createAdmin: function () {
        if (!this.admin_is_created) {
            this.deactivateButtons();
            $('.admin_creation_status').hide();
            $('#creating_admin').fadeIn();
            $.ajax({
                url: this.create_admin_url,
                type: 'POST',
                data: $('#adminForm').find('input').serializeArray(),
                dataType: 'json',
                success: function (datas) {
                    if (datas.success) {
                        $('#creating_admin').hide();
                        $('#admin_is_created').fadeIn();
                        this.admin_is_created = true;
                        setTimeout(this.nextStep.bind(this), 3000);
                    }
                }.bind(this),
                error: function (datas) {
                    var error;
                    try {
                        error = $.parseJSON(datas.responseText);
                        error = error.message;
                    } catch (e) {
                        error = 'Error';
                    }
                    $('#creating_admin').hide();
                    $('#admin_creation_has_failed').html('<i class="icon-remove"></i> ' + error).fadeIn();
                    this.checkButtons();
                }.bind(this),
                complete: function () {

                }
            });

            return false;
        }

        return true;
    },
    createApplication: function () {
        if (!this.application_is_created) {
            this.deactivateButtons();
            $('.application_creation_status').hide();
            $('#creating_application').fadeIn();
            $.ajax({
                url: this.create_application_url,
                type: 'POST',
                data: $('#appForm').find('input').serializeArray(),
                dataType: 'json',
                success: function (datas) {
                    if (datas.success) {
                        $('#creating_application').hide();
                        $('#application_is_created').fadeIn();
                        this.application_is_created = true;
                        this.installCron();
                        setTimeout(this.nextStep.bind(this), 3000);
                    }
                }.bind(this),
                error: function (datas) {
                    var error;
                    try {
                        error = $.parseJSON(datas.responseText);
                        error = error.message;
                    } catch (e) {
                        error = 'Error';
                    }
                    $('#creating_application').hide();
                    $('#admin_creation_has_failed').html('<i class="icon-remove"></i> ' + error).fadeIn();
                    this.checkButtons();
                }.bind(this),
                complete: function () {

                }
            });

            return false;
        }

        return true;
    },
    isInstalled: function () {
        return this.application_is_created && this.database_connection_is_set && this.is_installed && this.admin_is_created;
    }
};

var Step = Class.extend({
    id: null,
    init: function (id) {
        this.id = id;
        return this;
    },
    show: function () {
        $('.steps_content .step'+this.id).show();
        $('.title .step'+this.id).css({ transform: 'translate3d(0, 0, 0)', opacity: 1 });
        setTimeout(function () {
            $('.content .step'+this.id+' .part').css({ transform: 'translate3d(0, 0, 0)', opacity: 1 });
            this.isVisible();
        }.bind(this), 300);

        $('#step'+this.id+'_ok').css('opacity', 0);
    },
    hide: function (dir) {
        if (dir === 'next') {
            $('.title .step'+this.id).css({ transform: 'translate3d(-1000px, 0, 0)', opacity: 0 });
            setTimeout(function () {
                $('.content .step'+this.id+' .part1').css({ transform: 'translate3d(-350px, 0, 0)', opacity: 0 });
                $('.content .step'+this.id+' .part2').css({ transform: 'translate3d(850px, 0, 0)', opacity: 0 });
                setTimeout(function () {
                    $('.steps_content .step'+this.id).hide();
                }.bind(this), 650);
            }.bind(this), 200);
            $('#step'+this.id+'_ok').css('opacity', 1);
        } else {
            $('.title .step'+this.id).css({ transform: 'translate3d(1000px, 0, 0)', opacity: 0 });
            setTimeout(function () {
                $('.content .step'+this.id+' .part1').css({ transform: 'translate3d(0, -100px, 0)', opacity: 0 });
                $('.content .step'+this.id+' .part2').css({ transform: 'translate3d(0, 600px, 0)', opacity: 0 });
                setTimeout(function () {
                    $('.steps_content .step'+this.id).hide();
                }.bind(this), 650);
            }.bind(this), 200);
        }
    },
    canGoForward: function () {
        return true;
    },
    isVisible: function () {
        return this;
    }

});
