hack_generics_covariant_placeholder:
	  type { $$ = null; }
	| type T_AS name { $$ = null; }
;

hack_generics_placeholder_list:
	  hack_generics_covariant_placeholder
	| hack_generics_placeholder_list ',' hack_generics_covariant_placeholder
;

hack_non_optional_generics_placeholder_list:
	  '<' hack_generics_placeholder_list '>' { $$ = $2; }
;

hack_optional_generics_placeholder_list:
	  /* empty */ { $$ = null; }
	| '<' hack_generics_placeholder_list '>' { $$ = $2; }
;

hack_enum:
	  T_STRING '=' scalar ';' { $$ = Node\Const_[$1, $3]; }
;

hack_non_empty_enum_list:
	  hack_enum { init($1); }
	| hack_non_empty_enum_list hack_enum { push($1, $2); }
;

hack_enum_list:
	  /* empty */ { init(); }
	| hack_non_empty_enum_list { $$ = $1; }
;

hack_user_attribute:
	  name	{ $$ = PhackNode\UserAttribute[$1]; }
	| name '(' array_pair_list ')' { $$ = PhackNode\UserAttribute[$1, $3]; }
;

hack_user_attributes:
	  hack_user_attribute { $$ = init($1); }
	| hack_user_attributes ',' hack_user_attribute { $$ = push($1, $3); }
;

hack_user_attributes_list:
	  T_SL hack_user_attributes T_SR	{ $$ = $1; }
	| hack_user_attributes_list T_SL hack_user_attributes T_SR { $$ = $1 + $3; }
;

hack_lambda_arguments:
	  T_VARIABLE { $$ = init(Node\Param[parseVar($1), null]); }
	| T_LAMBDA_OP parameter_list T_LAMBDA_CP { $$ = $2; }
;

hack_lambda:
	  hack_lambda_arguments T_LAMBDA_ARROW expr { $$ = PhackExpr\Lambda[$1, init(Stmt\Return_[$3])]; }
	| hack_lambda_arguments T_LAMBDA_ARROW '{' inner_statement_list '}' { $$ = PhackExpr\Lambda[$1, $4]; }
;

type:
	  name '<' hack_generics_placeholder_list '>' { $$ = $this->handleScalarTypes($1); }
	| T_ARRAY '<' hack_generics_placeholder_list '>' { $$ = 'array'; }
;

class_declaration_statement:
	  class_entry_type T_STRING hack_non_optional_generics_placeholder_list
	  extends_from implements_list '{' class_statement_list '}'
	      { $$ = Stmt\Class_[$2, ['type' => $1, 'extends' => $4, 'implements' => $5, 'stmts' => $7]]; }
	| hack_user_attributes_list
	  class_entry_type T_STRING hack_optional_generics_placeholder_list
	  extends_from implements_list '{' class_statement_list '}'
	      { $$ = Stmt\Class_[$3, ['type' => $2, 'extends' => $5, 'implements' => $6, 'stmts' => $8]]; }

	| hack_user_attributes_list T_ENUM T_STRING ':' T_STRING '{' hack_enum_list '}'
	      { $$ = PhackStmt\Enum[$3, $5, $7]; }
	| T_ENUM T_STRING ':' T_STRING '{' hack_enum_list '}'
	      { $$ = PhackStmt\Enum[$2, $4, $6]; }
;

class_statement:
	  variable_modifiers type property_declaration_list ';' { $$ = Stmt\Property[$1, $3]; }

	| method_modifiers T_FUNCTION optional_ref identifier hack_non_optional_generics_placeholder_list
      '(' parameter_list ')' optional_return_type method_body
          { $$ = Stmt\ClassMethod[$4, ['type' => $1, 'byRef' => $3, 'params' => $7,
                                       'returnType' => $9, 'stmts' => $10]]; }
	| hack_user_attributes_list
	  method_modifiers T_FUNCTION optional_ref identifier hack_non_optional_generics_placeholder_list
      '(' parameter_list ')' optional_return_type method_body
          { $$ = Stmt\ClassMethod[$5, ['type' => $2, 'byRef' => $4, 'params' => $8,
                                       'returnType' => $10, 'stmts' => $11]]; }
;

function_declaration_statement:
	  T_FUNCTION optional_ref T_STRING hack_non_optional_generics_placeholder_list
	  '(' parameter_list ')' optional_return_type '{' inner_statement_list '}'
	      { $$ = Stmt\Function_[$3, ['byRef' => $2, 'params' => $6, 'returnType' => $8, 'stmts' => $10]]; }
	| hack_user_attributes_list
	  T_FUNCTION optional_ref T_STRING hack_optional_generics_placeholder_list
	  '(' parameter_list ')' optional_return_type '{' inner_statement_list '}'
	      { $$ = Stmt\Function_[$4, ['byRef' => $3, 'params' => $7, 'returnType' => $9,
	                                 'stmts' => $11, 'user_attributes' => $1]]; }
;

expr:
	  hack_lambda			                { $$ = $1; }
;
