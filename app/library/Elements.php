<?php

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Phalcon\Mvc\User\Component
{
    public function renderMenu(){
        $menuComponent = $this->di->getMenu();
//        var_dump($menuComponent);
//        exit
        $menuItems = $menuComponent->getModuleMenu();
        
        $lang = $this->di->getLang();
        if(!empty($menuItems)){
        ?>
            <ul class="nav" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element"> 
                            
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="clear"> 
                                    <span class="block m-t-xs">
                                        <strong class="font-bold"><?php echo $this->view->authUser->forename;?> <?php echo $this->view->authUser->surname;?></strong>
                                    </span> 
                                    <span class="text-muted text-xs block"><?php echo $this->view->authUser->role;?> <b class="caret"></b></span> 
                                </span> 
                            </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="/manager/user/my-profile/">Profile</a></li>
<!--                                <li><a href="contacts.html">Contacts</a></li>
                                <li><a href="mailbox.html">Mailbox</a></li>-->
                                <li class="divider"></li>
                                <li><a href="/manager/auth/logout/">Logout</a></li>
                            </ul>
                        </div>
<!--                        
                        <div class="logo-element">
                            <a href="/manager/" title="Chat App"><img src="/images/flip-flop/flipixo-logo-color-w.png" alt="FlipFlop CMS"></a>
                        </div>-->

                    </li>
            <?php
                foreach($menuItems as $resource => $resourceData){
                    if(isset($resourceData['name'])){
                    ?>                    
                    <li class="<?php echo (isset($resourceData['active']) && $resourceData['active'] === 1) ? 'active' : '';?>">
                        
                        <a href="<?php echo (empty($resourceData['url'])) ? '/' : '/'.$resourceData['url'].'/';?>">
                            <i class="<?php echo (isset($resourceData['css'])) ?$resourceData['css'] : '';?>"></i>
                            <span class="nav-label"><?php echo $lang->translate($resourceData['name']);?></span>
                        </a>
                        
                        <?php
                        if(isset($resourceData['items']) && !empty($resourceData['items'])){
                            ?>
                            <ul class='nav nav-second-level collapse  <?php echo ($resourceData['active'] === 1) ? 'in' : '';?>'>
                                <?php
                                foreach($resourceData['items'] as $actionData){
                                    ?>
                                    <li <?php echo ($actionData['active'] == 1) ? "" :"";?>>
                                        <a href="/<?php echo $actionData['url'].'/';?>"><?php echo $lang->translate($actionData['name']);?></a>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <?php
                        }
                        ?>
                    </li>
                    <?php
                    }
                }
            ?>
            </ul>
            <?php
        }
    }
}
