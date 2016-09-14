hack_generics_covariant_placeholder:
	  T_STRING { $$ = null; }
	| T_STRING T_AS T_STRING { $$ = null; }
;

hack_generics_placeholder_list:
	  hack_generics_covariant_placeholder
	| hack_generics_placeholder_list ',' hack_generics_covariant_placeholder
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

class_declaration_statement:
	  class_entry_type T_STRING '<' hack_generics_placeholder_list '>'
	  extends_from implements_list'{' class_statement_list '}'
	      { $$ = Stmt\Class_[$2, ['type' => $1, 'extends' => $6, 'implements' => $7, 'stmts' => $9]]; }
	| T_ENUM T_STRING ':' T_STRING '{' hack_enum_list '}'
	      { $$ = PhackStmt\Enum[$2, $4, $6]; }
;

function_declaration_statement:
	T_FUNCTION optional_ref T_STRING '<' hack_generics_placeholder_list '>'
	'(' parameter_list ')' optional_return_type '{' inner_statement_list '}'
	      { $$ = Stmt\Function_[$3, ['byRef' => $2, 'params' => $8, 'returnType' => $10, 'stmts' => $12]]; }
;

class_statement:
	  variable_modifiers T_STRING property_declaration_list ';' { $$ = Stmt\Property[$1, $3]; }
;

hack_lambda:
	  '(' ')' T_LAMBDA_ARROW expr           { $$ = PhackExpr\Lambda[init(), $4]; }
	| T_VARIABLE T_LAMBDA_ARROW expr        { $$ = PhackExpr\Lambda[init(Expr\Variable[parseVar($1)]), $3]; }
;

expr:
	  hack_lambda			                { $$ = $1; }
;
