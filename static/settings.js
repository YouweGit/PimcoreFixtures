Ext.onReady(function () {
    var generateFixtures = function () {
        formPanel.el.mask('Please wait', 'x-mask-loading');
        formPanel.getForm().submit({
            success: function () {
                new Ext.ux.Notification({
                    iconCls: 'icon_notification_success',
                    title: 'Success',
                    html: 'Fixtures file has been generated!',
                    autoDestroy: true,
                    hideDelay: 2000
                }).show(document);
                formPanel.el.unmask();
            },
            failure: function(){
                new Ext.ux.Notification({
                    iconCls: 'icon_notification_error',
                    title: 'Error',
                    html: 'Something went wrong, please retry!',
                    autoDestroy: true,
                    hideDelay: 2000
                }).show(document);
                formPanel.el.unmask();
            }
        });
    };

    var loadFixtures = function () {
        loadPanel.el.mask('Please wait', 'x-mask-loading');
        loadPanel.getForm().submit({
            success: function (response) {
                new Ext.ux.Notification({
                    iconCls: 'icon_notification_success',
                    title: 'Success',
                    html: 'Fixtures file has been loaded!',
                    autoDestroy: true,
                    hideDelay: 2000
                }).show(document);
                loadPanel.el.unmask();
            },
            failure: function(){
                new Ext.ux.Notification({
                    iconCls: 'icon_notification_error',
                    title: 'Error',
                    html: 'Something went wrong, please retry!',
                    autoDestroy: true,
                    hideDelay: 2000
                }).show(document);
                loadPanel.el.unmask();
            }
        });
    };
    var formPanel = new Ext.FormPanel({
        frame: true,
        title: 'Generate fixtures (beta)',
        labelAlign: 'right',
        labelWidth: 150,
        width: 750,
        url: '/plugin/PimcoreFixtures/admin/generate-fixtures',
        waitMsg: 'Saving Data...',
        submitEmptyText: true,
        items: [
            new Ext.form.FieldSet({
                title: 'Generate fixtures from folder path',
                autoHeight: true,
                items: [
                    new Ext.form.ComboBox({
                        fieldLabel: 'Object path',
                        hiddenName: 'id',
                        store: new Ext.data.JsonStore({
                            autoDestroy: true,
                            url: '/plugin/PimcoreFixtures/admin/get-folder-path',
                            storeId: 'folders',
                            root: 'data',
                            idProperty: 'id',
                            fields: ['id', 'fullPath']
                        }),
                        valueField: 'id',
                        displayField: 'fullPath',
                        typeAhead: true,
                        minChars: 0,
                        queryDelay: 200,
                        mode: 'remote',
                        autoLoad: true,
                        triggerAction: 'all',
                        emptyText: 'Select a path...',
                        selectOnFocus: true,
                        width: 500,
                        allowBlank: false
                    }),
                    {
                        emptyText: 'object_name (must be lowercase divided by underline, snake_case)',
                        width: 500,
                        xtype: 'textfield',
                        fieldLabel: 'Object name',
                        name: 'filename',
                        allowBlank: false,
                        validator: function(v) {
                            return !(v.match(/^[a-z0-9_]*$/) === null);
                        }
                    },
                    {
                        emptyText: 'How many levels deep should loop ...',
                        width: 500,
                        xtype: 'numberfield',
                        fieldLabel: 'Max levels deep',
                        name: 'levels',
                        allowBlank: false,
                        value: 1,
                        maxValue: 99,
                        minValue: 1
                    }
                ]
            })
        ],
        buttons: [
            {
                text: 'Generate',
                handler: generateFixtures
            }
        ]
    });
    var loadPanel = new Ext.FormPanel({
        frame: true,
        title: 'Load fixtures',
        labelAlign: 'right',
        labelWidth: 85,
        width: 750,
        url: '/plugin/PimcoreFixtures/admin/load-fixtures',
        waitMsg: 'Loading Data...',
        buttons:[{
            text:'Load Fixtures',
            handler: loadFixtures
        }]
    });

    var window = new Ext.Panel({
        items: [
            formPanel,
            loadPanel
        ]
    });
    new Ext.Viewport({
        layout: 'fit',
        items: [window]
    });
});