<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
//            $grid->column('email');
//            $grid->column('email_verified_at');
//            $grid->column('password');
//            $grid->column('remember_token');
            $grid->column('mobile');
            $grid->column('nickname');
            $grid->column('parent_id');
            $grid->column('status')->using([
                0 => '启用',
                1 => '禁用',
            ], '未知')->label([
                0 => 'success',
                1 => 'danger',
            ], 'warning');
            $grid->column('last_login_at');
            $grid->column('last_login_ip');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('email');
            $show->field('email_verified_at');
            $show->field('password');
            $show->field('remember_token');
            $show->field('mobile');
            $show->field('nickname');
            $show->field('parent_id');
            $show->field('status');
            $show->field('last_login_at');
            $show->field('last_login_ip');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->text('name');
//            $form->text('email');
//            $form->text('email_verified_at');
//            $form->text('password');
//            $form->text('remember_token');
//            $form->text('mobile');

            if ($form->isEditing()) {
                $form->mobile('mobile');
                $form->password('password')->help('不修改密码无需填写 默认密码 123456789');
            }
            if ($form->isCreating()) {
                $form->mobile('mobile')->required();
                $form->password('password')->default('123456')->required()->help('默认密码 123456');
            }

            $form->text('nickname');
            $form->text('parent_id');
//            $form->text('status');
            $form->switch('status');
            $form->distpicker(['province_id', 'city_id', 'district_id'])->type('code')->select2();
            $form->display('last_login_at');
            $form->display('last_login_ip');
            $form->display('created_at');
            $form->display('updated_at');

            // 保存前回调 设置未填写参数的默认值
            $form->saving(function (Form $form) {
                if (empty($form->nickname)) {
                    $form->nickname = $form->mobile;
                }
                if (empty($form->name)) {
                    $form->name = $form->mobile;
                }

                // 密码修改
                if (empty($form->input('password'))) {
                    $form->input('password', $form->model()->password);
                } elseif ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });

            $form->tools(function (Form\Tools $tools) {
                //            $tools->disableList(); // 去掉`列表`按钮
                $tools->disableDelete(); // 去掉`删除`按钮
                $tools->disableView(); // 去掉`查看`按钮
            });
            $form->footer(function ($footer) {
                            $footer->disableReset();  // 去掉`重置`按钮
                //            $footer->disableSubmit();   // 去掉`提交`按钮
                $footer->disableViewCheck(); // 去掉`查看`checkbox
                $footer->disableEditingCheck();  // 去掉`继续编辑`checkbox
                $footer->disableCreatingCheck();// 去掉`继续创建`checkbox
            });

        });
    }
}
