Ext.onReady(function () {
    var generateFixtures = function () {
        var generateFixturesPanel = Ext.getCmp('generateFixturesPanel');
        generateFixturesPanel.el.mask('Please wait', 'x-mask-loading');
        generateFixturesPanel.getForm().submit({
            success: function () {
                Ext.create('Ext.window.Toast', {
                    iconCls: 'pimcore_icon_accept',
                    title: 'Success',
                    html: 'Fixtures file has been generated!',
                    autoShow: true
                });
                generateFixturesPanel.el.unmask();
            },
            failure: function () {
                Ext.create('Ext.window.Toast', {
                    iconCls: 'pimcore_icon_error',
                    title: 'Error',
                    html: 'Something went wrong, please retry!',
                    autoShow: true
                });
                generateFixturesPanel.el.unmask();
            }
        });
    };

    var loadFixtures = function () {
        var loadPanel = Ext.getCmp('loadFixturesPanel');
        loadPanel.el.mask('Please wait', 'x-mask-loading');
        loadPanel.getForm().submit({
            success: function (response) {
                Ext.create('Ext.window.Toast', {
                    iconCls: 'pimcore_icon_accept',
                    title: 'Success',
                    html: 'Fixtures file has been loaded!',
                    autoShow: true
                });
                loadPanel.el.unmask();
            },
            failure: function () {
                Ext.create('Ext.window.Toast', {
                    iconCls: 'pimcore_icon_error',
                    title: 'Error',
                    html: 'Something went wrong, please retry!',
                    autoShow: true
                });
                loadPanel.el.unmask();
            }
        });
    };

    function getGenerateFixturesPanel() {
        return Ext.create('Ext.form.Panel', {
            id: 'generateFixturesPanel',
            frame: true,
            title: 'Generate fixtures (beta)',
            labelAlign: 'right',
            labelWidth: 150,
            width: 750,
            url: '/plugin/PimcoreFixtures/admin/generate-fixtures',
            waitMsg: 'Saving Data...',
            submitEmptyText: true,
            items: [
                Ext.create('Ext.form.FieldSet', {
                    title: 'Generate fixtures from folder path',
                    autoHeight: true,
                    items: [
                        Ext.create('Ext.form.ComboBox', {
                            fieldLabel: 'Object path',
                            name: 'folderId',

                            store: Ext.create('Ext.data.JsonStore', {
                                autoDestroy: true,
                                autoLoad: true,
                                proxy: {
                                    type: 'ajax',
                                    url: '/plugin/PimcoreFixtures/admin/get-folder-by-path',
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'folders'
                                    }
                                },
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
                            validator: function (v) {
                                var valid = !(v.match(/^[a-z0-9_]*$/) === null);
                                if (!valid) {
                                    return 'Value must be snake case'
                                }
                                return valid;
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
                            minValue: 1,
                            mouseWheelEnabled: false
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
    }

    function getLoadFixturesPanel() {
        return Ext.create('Ext.form.Panel', {
            id: 'loadFixturesPanel',
            frame: true,
            title: 'Load fixtures',
            labelAlign: 'right',
            labelWidth: 85,
            width: 750,
            url: '/plugin/PimcoreFixtures/admin/load-fixtures',
            waitMsg: 'Loading Data...',
            buttons: [{
                text: 'Load Fixtures',
                handler: loadFixtures
            }]
        });
    }

    Ext.create('Ext.container.Viewport', {
        layout: 'fit',
        items: [
            {
                region: 'center',
                items: [
                    getGenerateFixturesPanel(),
                    getLoadFixturesPanel()
                ]
            }
        ]
    });
});