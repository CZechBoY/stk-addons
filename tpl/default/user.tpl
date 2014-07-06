{config_load file="{$smarty.current_dir}/tpl.conf"}
{include file=#header#}
<div class="container">
    <div class="row">
        <div class="col-sm-2 col-md-2 left-menu">
            <ul class="list-group">
                {foreach $user.items as $item}
                    {$class=""}
                    {if $item.active == 0}
                        {$class=" unavailable"}
                    {/if}
                    <li class="list-group-item">
                        <a href="users.php?user={$item.username|escape}" class="user-list{$class}">
                            <img class="icon" src="{$img_location}user.png">
                            <span>{$item.username|escape|truncate:24}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
        <div class="col-sm-10 col-md-10">
            <div id="user-status">
                {$user.status}
            </div>
            <div id="user-body">
                {$user.body}
            </div>
        </div>
    </div>
</div>
{include file=#footer#}