function remove_font_awesome_useless_files {
  if
    rm -f ./node_modules/angular-font-awesome/angular-font-awesome.d.ts &&
    rm -f ./node_modules/angular-font-awesome/angular-font-awesome.js &&
    rm -f ./node_modules/angular-font-awesome/angular-font-awesome.js.map &&
    rm -f ./node_modules/angular-font-awesome/index.d.ts &&
    rm -f ./node_modules/angular-font-awesome/index.js &&
    rm -f ./node_modules/angular-font-awesome/index.js.map;
  then
    echo "Angular-font-awesome: ${green}Useless files correctly removed${normal}"
  else
    echo "Angular-font-awesome: ${red}Useless files not found${normal}"
  fi
}

remove_font_awesome_useless_files
