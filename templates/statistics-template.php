<link rel="stylesheet" type="text/css"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
      href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/bootstrap-extended.min.css">
<link rel="stylesheet" type="text/css"
      href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/fonts/simple-line-icons/style.min.css">
<link rel="stylesheet" type="text/css"
      href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/colors.min.css">
<link rel="stylesheet" type="text/css"
      href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">

<style>
    .media-body {
        padding: 10px;
    }

    .mr-2, .mx-2 {
        margin-right: -2.5rem !important;
    }
</style>

<div class="grey-bg container-fluid">
    <section id="minimal-statistics">
        <div class="row">
            <div class="col-12 mt-3 mb-1" style="text-align: right;">
                <h4 class="text-uppercase">وحدة الإحصاء و المقاييس</h4>
                <p><b> يعتمد مدى دقة البيانات و المعلومات في هذا التقرير على مدى دقة البيانات المدخلة في النظام </b>
                </p>
                <p>v2.2.0 (r25)</p>

                <p class="text-muted">
                    <?php
                    $last_refresh = get_option('tamincom_stats_last_cron');
                    if ($last_refresh && isset($last_refresh['timestamp'])) {
                        echo ' آخر تحديث تلقائي للإحصائيات منذ: ';
                        echo human_time_diff($last_refresh['timestamp']);
                    }
                    ?>
                </p>
                <p class="text-muted" style="color: red !important;">
                    ملاحظة: البيانات المعروضة قد تختلف قليلاً عن البيانات الفعلية (إلا في حال التحديث اليدوي).
                </p>

                <form method="get" action="<?php echo admin_url('admin.php'); ?>" style="margin-top: 10px;" id="refresh-stats-form">
                    <input type="hidden" name="page" value="stats/main.php">
                    <input type="hidden" name="force_refresh" value="<?php echo time(); ?>">
                    <button
                            type="submit"
                            id="refresh-stats-button"
                            style="background-color: #dc3545;
                            color: white;
                            padding: 8px 16px;
                            border-radius: 20px;
                            border: none;
                            font-weight: bold;
                            cursor: pointer;">
                        تحديث البيانات الآن
                    </button>
                </form>

            </div>

        </div>
        <div class="row">

            <div class="col-xl-3 col-sm-6 col-12">

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="primary"><?php echo $grand_total_contract; ?></h3>
                                    <span>إجمالي عدد العقود</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-book-open primary font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="danger"><?php echo $total_active_contracts; ?></h3>
                                    <span>عقود فعالة</span>
                                    <br>
                                    <br>
                                    <h3 class="danger"><?php echo $active_pct.'%'; ?></h3>
                                    <span>النسبة من إجمالي العقود</span>
                                    <br>
                                    <br>
                                    <span>منها</span>
                                    <br>

                                    <h3 class="danger"><?php echo $new_pct.'%'.' ('.$contract_data['new_count'].')'; ?></h3>
                                    <span>عقود جديدة</span>
                                    <br>
                                    <br>
                                    <h3 class="danger"><?php echo $renew_pct.'%'.' ('.$contract_data['renew_count'].')'; ?></h3>
                                    <span>تجديدات</span>

                                    <!--                       Num of cycles for active contracts                     -->

                                    <br>
                                    <br>
                                    <h3 class="danger"><?php echo $contract_data['cycles_total']; ?></h3>
                                    <span>عدد الدورات الكلي</span>

                                </div>
                                <div class="align-self-center">
                                    <i class="icon-rocket danger font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="danger"><?php echo $inactive_contracts; ?></h3>
                                    <span>عقود غير فعالة</span>
                                    <!--                       Num of cycles for inactive contracts                     -->

                                    <br>
                                    <br>
                                    <h3 class="danger"><?php echo $inact_cycle_total; ?></h3>
                                    <span>عدد الدورات الكلي</span>

                                </div>
                                <div class="align-self-center">
                                    <i class="icon-direction danger font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="success"><?php echo $grand_total_client; ?></h3>
                                    <span>إجمالي عدد العملاء</span>
                                    <br>
                                    <br>
                                    <span>عملاء فعالة</span>
                                    <h3 class="success"><?php echo $active_client_count; ?></h3>

                                    <br>
                                    <span>النسبة من إجمالي العملاء</span>
                                    <h3 class="success"><?php echo $active_pct_client.'%'; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-user success font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <span>أفراد</span> <br>
                                    <h3 class="warning"><?php echo $ind_pct.'% ('.$client_ind_count.')'; ?></h3>
                                    <br>
                                    <span>مؤسسات</span> <br>
                                    <h3 class="warning"><?php echo $bus_pct.'% ('.$client_bus_count.')'; ?></h3>
                                    <br>
                                    <span>التوزيع النوعي للعملاء - اجمالي</span>
                                    <br>
                                    <br>
                                    <span>أفراد</span> <br>
                                    <h3 class="warning"><?php echo $active_pct_client_ind.'% ('.$demographics['ind'].')'; ?></h3>
                                    <br>
                                    <span>مؤسسات</span> <br>
                                    <h3 class="warning"><?php echo $active_pct_client_bus.'% ('.$demographics['bus'].')'; ?></h3>
                                    <br>
                                    <span>التوزيع النوعي للعملاء - فعالة</span>


                                </div>
                                <div class="align-self-center">
                                    <i class="icon-pie-chart warning font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <span>ذكر</span> <br>
                                    <h3 class="warning"><?php echo $male_pct.'% ('.$client_male_count.')'; ?></h3>
                                    <br>
                                    <span>أنثى</span> <br>
                                    <h3 class="warning"><?php echo $female_pct.'% ('.$client_female_count.')'; ?></h3>
                                    <br>
                                    <span>توزيع العملاء حسب الجنس - إجمالي</span>
                                    <br>
                                    <br>
                                    <span>ذكر</span> <br>
                                    <h3 class="warning"><?php echo $active_pct_client_male.'% ('.$demographics['active_male'].')'; ?></h3>
                                    <br>
                                    <span>أنثى</span> <br>
                                    <h3 class="warning"><?php echo $active_pct_client_female.'% ('.$demographics['active_female'].')'; ?></h3>
                                    <br>
                                    <span>توزيع العملاء حسب الجنس - فعالة</span>

                                </div>
                                <div class="align-self-center">
                                    <i class="icon-pie-chart warning font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xlc-12 col-md-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-right">
                                    <h3 class="primary"><?php echo $hi_risk_post_count; ?></h3>
                                    <span>عملاء بخطورة عالية</span>
                                </div>

                                <div class="align-self-center">
                                    <i class="icon-support primary font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="text-right">
                                <a href='/wp-admin/edit.php?s&post_status=all&post_type=client&m=0&layout=5fb407e0e49b5&acp_filter%5B5cbc513589de9%5D=hi&filter_action=تصفية&action=-1&paged=1&action2=-1'
                                   target="_blank"><span>استعراض التفاصيل</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <i class="icon-graph success font-large-2 float-left"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3><?php echo "JD ".$value_avg_print; ?></h3>
                                    <span>الوسط الحسابي لمبلغ التأمين</span>
                                    <span>للمركبات</span>
                                    <br>
                                    <span>عقود فعالة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <i class="icon-graph success font-large-2 float-left"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3><?php echo "JD ".$payment_avg_print; ?></h3>
                                    <span>الوسط الحسابي لقسط التأمين</span>
                                    <span>للمركبات</span>
                                    <br>
                                    <span>عقود فعالة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xlc-12 col-md-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-right">
                                    <span>توزيع العقود حسب مزود التأمين</span>
                                    <br> <br>
                                    <span> الشرق الأوسط - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['me'].'% ('.$contract_data['providers']['me']['count'].')'.' '.number_format($contract_data['providers']['me']['sum'],
                                                0, ".", ",").'  JD'; ?></h3>
                                    <br>

                                    <span>المتبقي للتحصيل</span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['me']['pending'], 0, ".",
                                                ","); ?></h3>
                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?s&post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"الشرق+الأوسط"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&action=-1&paged=1&action2=-1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>
                                    <hr>
                                    <br>

                                    <span>العربية الأوروبية - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['ae'].'% ('.$contract_data['providers']['ae']['count'].')'.' '.number_format($contract_data['providers']['ae']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>
                                    <span>المتبقي للتحصيل</span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['ae']['pending'], 0, ".",
                                                ","); ?></h3>
                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?s&post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"العربية+الاوروبية"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&action=-1&paged=1&action2=-1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>
                                    <hr>
                                    <br>

                                    <span>القدس - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['qds'].'% ('.$contract_data['providers']['qds']['count'].')'.' '.number_format($contract_data['providers']['qds']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>
                                    <span>المتبقي للتحصيل </span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['qds']['pending'], 0, ".",
                                                ","); ?></h3>
                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?s&post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"القدس"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&action=-1&paged=1&action2=-1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>
                                    <hr>
                                    <br>
                                    <span>العرب - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['arab'].'% ('.$contract_data['providers']['arab']['count'].')'.' '.number_format($contract_data['providers']['arab']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>

                                    <span>المتبقي للتحصيل</span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['arab']['pending'], 0, ".",
                                                ","); ?></h3>

                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?s&post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"العرب+للتأمين"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&action=-1&paged=1&action2=-1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>
                                    <hr>
                                    <br>

                                    <span>الأولى - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['sld'].'% ('.$contract_data['providers']['sld']['count'].')'.' '.number_format($contract_data['providers']['sld']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>

                                    <span>المتبقي للتحصيل</span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['sld']['pending'], 0, ".",
                                                ","); ?></h3>
                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"الأولى+للتأمين"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&paged=1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>
                                    <hr>
                                    <br>

                                    <span>العربية الأردنية - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['arjo'].'% ('.$contract_data['providers']['arjo']['count'].')'.' '.number_format($contract_data['providers']['arjo']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>

                                    <span>المتبقي للتحصيل</span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['arjo']['pending'], 0, ".",
                                                ","); ?></h3>

                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"العربية+الأردنية"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&paged=1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>

                                    <hr>
                                    <br>

                                    <span>المتوسط والخليج للتأمين - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['ag'].'% ('.$contract_data['providers']['ag']['count'].')'.' '.number_format($contract_data['providers']['ag']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>

                                    <span>المتبقي للتحصيل</span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['ag']['pending'], 0, ".",
                                                ","); ?></h3>

                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?orderby=610d0784c75b1&order=asc&s&post_status=all&post_type=contract&ac-rules=%7B"condition"%3A"AND"%2C"rules"%3A%5B%7B"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"%7D%2C%7B"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"المتوسط+والخليج+للتأمين"%7D%5D%2C"valid"%3Atrue%7D&m=0&layout=5fb407e0e4987&filter_action=Filter&action=-1&paged=1&action2=-1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>

                                    <hr>
                                    <br>

                                    <span>المنارة الإسلامية - عقود فعالة</span> <br>
                                    <h3 class="warning"><?php echo $provider_percentages['mnr'].'% ('.$contract_data['providers']['mnr']['count'].')'.' '.number_format($contract_data['providers']['mnr']['sum'],
                                                0, ".", ",").' JD'; ?></h3>
                                    <br>
                                    <span>المتبقي للتحصيل </span>
                                    <h3 class="warning"> <?php echo "JD ".number_format($contract_data['providers']['mnr']['pending'], 0, ".",
                                                ","); ?></h3>
                                    <div style="display:inline;">
                                        <a href='/wp-admin/edit.php?s&post_status=all&post_type=contract&ac-rules={"condition"%3A"AND"%2C"rules"%3A[{"id"%3A"5cdeb82646c69"%2C"field"%3A"5cdeb82646c69"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"not_equal"%2C"value"%3A"نعم"}%2C{"id"%3A"5ccaaff5850ec"%2C"field"%3A"5ccaaff5850ec"%2C"type"%3A"string"%2C"input"%3A"text"%2C"operator"%3A"equal"%2C"value"%3A"المنارة الإسلامية"}]%2C"valid"%3Atrue}&m=0&layout=5fb407e0e4987&filter_action=تصفية&action=-1&paged=1&action2=-1'
                                           target="_blank"><span>استعراض التفاصيل</span></a>
                                    </div>

                                    <hr>
                                    <br>


                                    <span>توزيع العقود حسب مزود التأمين</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-pie-chart warning font-large-2 float-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <h3 class="mr-2"><?php echo "JD ".$sum_print; ?></h3>
                                </div>
                                <div class="media-body">
                                    <h4>مجموع المبالغ</h4>
                                    <span>عقود فعالة</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-wallet success font-large-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <h3 class="mr-2"><?php echo "JD ".$payment_print; ?></h3>
                                </div>
                                <div class="media-body">
                                    <h4>مجموع الأقساط</h4>
                                    <span>عقود فعالة</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-wallet success font-large-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <h3 class="mr-2"><?php echo "JD ".$claimed_print; ?></h3>
                                </div>
                                <div class="media-body">
                                    <h4>المحصل</h4>
                                    <span>عقود فعالة</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-wallet success font-large-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xlc-6 col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body cleartfix">
                            <div class="media align-items-stretch">
                                <div class="align-self-center">
                                    <h3 class="mr-2"><?php echo "JD ".number_format($contract_data['unclaimed_grand'], 0, ".",
                                                ","); ?></h3>
                                </div>
                                <div class="media-body">
                                    <h4>الغير محصل</h4>
                                    <span>عقود فعالة</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="icon-wallet success font-large-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--

  <div class="col-xlc-6 col-md-6">
    <div class="card">
      <div class="card-content">
        <div class="card-body cleartfix">
          <div class="media align-items-stretch">
            <div class="align-self-center">
              <h1 class="mr-2"><?php //echo $newclaimed_pct . "% ";
            ?></h1>
            </div>
            <div class="media-body">
              <h4>نسبة التحصيل</h4>
              <span>عقود فعالة و منتهية</span>
            </div>
            <div class="align-self-center">
              <i class="icon-wallet success font-large-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

-->

        </div>
    </section>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('refresh-stats-form');
        var button = document.getElementById('refresh-stats-button');
        
        form.addEventListener('submit', function() {
            button.innerHTML = 'جاري التحديث...';
            button.disabled = true;
            button.style.opacity = '0.7';
            button.style.cursor = 'not-allowed';
        });
    });
</script>