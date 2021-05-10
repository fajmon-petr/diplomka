!COMMENT!
Enter your description of the rulebase here.
!END_COMMENT!

TypeOfDescription=linguistic
InfMethod=Logical_Deduction
DefuzzMethod=SimpDefuzzLingExpression
UseFuzzyFilter=false

NumberOfAntecedentVariables=4
NumberOfSuccedentVariables=1
NumberOfRules=135

AntVariable1
 name=V1
 settings=new
 context=<0,0.5,1>
 discretization=301
 UserTerm
  name=sm
  type=trapezoid
  parameters= 0 0 0.25 0.4
 End_UserTerm
 UserTerm
  name=me
  type=trapezoid
  parameters= 0.25 0.4 0.6 0.75
 End_UserTerm
 UserTerm
  name=bi
  type=trapezoid
  parameters= 0.6 0.75 1 1
 End_UserTerm
End_AntVariable1

AntVariable2
 name=V2
 settings=new
 context=<0,0.5,1>
 discretization=301
 UserTerm
  name=sm
  type=trapezoid
  parameters= 0 0 0.25 0.4
 End_UserTerm
 UserTerm
  name=me
  type=trapezoid
  parameters= 0.25 0.4 0.6 0.75
 End_UserTerm
 UserTerm
  name=bi
  type=trapezoid
  parameters= 0.6 0.75 1 1
 End_UserTerm
End_AntVariable2

AntVariable3
 name=V3
 settings=new
 context=<0,0.5,1>
 discretization=301
 UserTerm
  name=vesm
  type=trapezoid
  parameters= 0 0 0.15 0.25
 End_UserTerm
 UserTerm
  name=sm
  type=trapezoid
  parameters= 0.15 0.25 0.35 0.45
 End_UserTerm
 UserTerm
  name=me
  type=trapezoid
  parameters= 0.35 0.45 0.55 0.65
 End_UserTerm
 UserTerm
  name=bi
  type=trapezoid
  parameters= 0.55 0.65 0.75 0.85
 End_UserTerm
 UserTerm
  name=vebi
  type=trapezoid
  parameters= 0.75 0.85 1 1
 End_UserTerm
End_AntVariable3

AntVariable4
 name=V4
 settings=new
 context=<0,0.5,1>
 discretization=301
 UserTerm
  name=sm
  type=trapezoid
  parameters= 0 0 0.25 0.4
 End_UserTerm
 UserTerm
  name=me
  type=trapezoid
  parameters= 0.25 0.4 0.6 0.75
 End_UserTerm
 UserTerm
  name=bi
  type=trapezoid
  parameters= 0.6 0.75 1 1
 End_UserTerm
End_AntVariable4

SucVariable1
 name=V5
 settings=new
 context=<0,0.4,1>
 discretization=301
 UserTerm
  name=exsm
  type=trapezoid
  parameters= 0 0 0.075 0.145
 End_UserTerm
 UserTerm
  name=vesm
  type=trapezoid
  parameters= 0.075 0.145 0.205 0.275
 End_UserTerm
 UserTerm
  name=sm
  type=trapezoid
  parameters= 0.205 0.275 0.335 0.405
 End_UserTerm
 UserTerm
  name=mlm
  type=trapezoid
  parameters= 0.335 0.405 0.465 0.535
 End_UserTerm
 UserTerm
  name=me
  type=trapezoid
  parameters= 0.465 0.535 0.595 0.665
 End_UserTerm
 UserTerm
  name=bi
  type=trapezoid
  parameters= 0.595 0.665 0.725 0.795
 End_UserTerm
 UserTerm
  name=vebi
  type=trapezoid
  parameters= 0.725 0.795 0.855 0.925
 End_UserTerm
 UserTerm
  name=exbi
  type=trapezoid
  parameters= 0.855 0.925 1 1
 End_UserTerm
End_SucVariable1

RULES
 "sm" "sm" "vesm" "sm" | "sm"
 "me" "sm" "vesm" "sm" | "vesm"
 "bi" "sm" "vesm" "sm" | "exsm"
 "sm" "me" "vesm" "sm" | "sm"
 "me" "me" "vesm" "sm" | "vesm"
 "bi" "me" "vesm" "sm" | "exsm"
 "sm" "bi" "vesm" "sm" | "sm"
 "me" "bi" "vesm" "sm" | "vesm"
 "bi" "bi" "vesm" "sm" | "exsm"
 "sm" "sm" "sm" "sm" | "sm"
 "me" "sm" "sm" "sm" | "vesm"
 "bi" "sm" "sm" "sm" | "exsm"
 "sm" "me" "sm" "sm" | "sm"
 "me" "me" "sm" "sm" | "vesm"
 "bi" "me" "sm" "sm" | "exsm"
 "sm" "bi" "sm" "sm" | "me"
 "me" "bi" "sm" "sm" | "vesm"
 "bi" "bi" "sm" "sm" | "exsm"
 "sm" "sm" "me" "sm" | "me"
 "me" "sm" "me" "sm" | "sm"
 "bi" "sm" "me" "sm" | "vesm"
 "sm" "me" "me" "sm" | "me"
 "me" "me" "me" "sm" | "sm"
 "bi" "me" "me" "sm" | "vesm"
 "sm" "bi" "me" "sm" | "mlm"
 "me" "bi" "me" "sm" | "sm"
 "bi" "bi" "me" "sm" | "vesm"
 "sm" "sm" "bi" "sm" | "bi"
 "me" "sm" "bi" "sm" | "me"
 "bi" "sm" "bi" "sm" | "mlm"
 "sm" "me" "bi" "sm" | "bi"
 "me" "me" "bi" "sm" | "me"
 "bi" "me" "bi" "sm" | "sm"
 "sm" "bi" "bi" "sm" | "me"
 "me" "bi" "bi" "sm" | "mlm"
 "bi" "bi" "bi" "sm" | "sm"
 "sm" "sm" "vebi" "sm" | "vebi"
 "me" "sm" "vebi" "sm" | "bi"
 "bi" "sm" "vebi" "sm" | "mlm"
 "sm" "me" "vebi" "sm" | "bi"
 "me" "me" "vebi" "sm" | "mlm"
 "bi" "me" "vebi" "sm" | "me"
 "sm" "bi" "vebi" "sm" | "vebi"
 "me" "bi" "vebi" "sm" | "mlm"
 "bi" "bi" "vebi" "sm" | "sm"
 "sm" "sm" "vesm" "me" | "sm"
 "me" "sm" "vesm" "me" | "vesm"
 "bi" "sm" "vesm" "me" | "exsm"
 "sm" "me" "vesm" "me" | "me"
 "me" "me" "vesm" "me" | "sm"
 "bi" "me" "vesm" "me" | "vesm"
 "sm" "bi" "vesm" "me" | "me"
 "me" "bi" "vesm" "me" | "me"
 "bi" "bi" "vesm" "me" | "me"
 "sm" "sm" "sm" "me" | "mlm"
 "me" "sm" "sm" "me" | "sm"
 "bi" "sm" "sm" "me" | "vesm"
 "sm" "me" "sm" "me" | "mlm"
 "me" "me" "sm" "me" | "sm"
 "bi" "me" "sm" "me" | "vesm"
 "sm" "bi" "sm" "me" | "me"
 "me" "bi" "sm" "me" | "vesm"
 "bi" "bi" "sm" "me" | "exsm"
 "sm" "sm" "me" "me" | "bi"
 "me" "sm" "me" "me" | "me"
 "bi" "sm" "me" "me" | "me"
 "sm" "me" "me" "me" | "me"
 "me" "me" "me" "me" | "mlm"
 "bi" "me" "me" "me" | "sm"
 "sm" "bi" "me" "me" | "me"
 "me" "bi" "me" "me" | "sm"
 "bi" "bi" "me" "me" | "vesm"
 "sm" "sm" "bi" "me" | "vebi"
 "me" "sm" "bi" "me" | "bi"
 "bi" "sm" "bi" "me" | "me"
 "sm" "me" "bi" "me" | "vebi"
 "me" "me" "bi" "me" | "bi"
 "bi" "me" "bi" "me" | "me"
 "sm" "bi" "bi" "me" | "bi"
 "me" "bi" "bi" "me" | "me"
 "bi" "bi" "bi" "me" | "mlm"
 "sm" "sm" "vebi" "me" | "exbi"
 "me" "sm" "vebi" "me" | "vebi"
 "bi" "sm" "vebi" "me" | "bi"
 "sm" "me" "vebi" "me" | "exbi"
 "me" "me" "vebi" "me" | "bi"
 "bi" "me" "vebi" "me" | "me"
 "sm" "bi" "vebi" "me" | "bi"
 "me" "bi" "vebi" "me" | "me"
 "bi" "bi" "vebi" "me" | "mlm"
 "sm" "sm" "vesm" "bi" | "mlm"
 "me" "sm" "vesm" "bi" | "sm"
 "bi" "sm" "vesm" "bi" | "vesm"
 "sm" "me" "vesm" "bi" | "mlm"
 "me" "me" "vesm" "bi" | "sm"
 "bi" "me" "vesm" "bi" | "vesm"
 "sm" "bi" "vesm" "bi" | "me"
 "me" "bi" "vesm" "bi" | "sm"
 "bi" "bi" "vesm" "bi" | "exsm"
 "sm" "sm" "sm" "bi" | "me"
 "me" "sm" "sm" "bi" | "mlm"
 "bi" "sm" "sm" "bi" | "sm"
 "sm" "me" "sm" "bi" | "me"
 "me" "me" "sm" "bi" | "sm"
 "bi" "me" "sm" "bi" | "vesm"
 "sm" "bi" "sm" "bi" | "me"
 "me" "bi" "sm" "bi" | "vesm"
 "bi" "bi" "sm" "bi" | "exsm"
 "sm" "sm" "me" "bi" | "bi"
 "me" "sm" "me" "bi" | "me"
 "bi" "sm" "me" "bi" | "mlm"
 "sm" "me" "me" "bi" | "bi"
 "me" "me" "me" "bi" | "me"
 "bi" "me" "me" "bi" | "mlm"
 "sm" "bi" "me" "bi" | "vebi"
 "me" "bi" "me" "bi" | "me"
 "bi" "bi" "me" "bi" | "sm"
 "sm" "sm" "bi" "bi" | "vebi"
 "me" "sm" "bi" "bi" | "bi"
 "bi" "sm" "bi" "bi" | "me"
 "sm" "me" "bi" "bi" | "vebi"
 "me" "me" "bi" "bi" | "me"
 "bi" "me" "bi" "bi" | "mlm"
 "sm" "bi" "bi" "bi" | "vebi"
 "me" "bi" "bi" "bi" | "me"
 "bi" "bi" "bi" "bi" | "sm"
 "sm" "sm" "vebi" "bi" | "exbi"
 "me" "sm" "vebi" "bi" | "vebi"
 "bi" "sm" "vebi" "bi" | "bi"
 "sm" "me" "vebi" "bi" | "exbi"
 "me" "me" "vebi" "bi" | "vebi"
 "bi" "me" "vebi" "bi" | "bi"
 "sm" "bi" "vebi" "bi" | "exbi"
 "me" "bi" "vebi" "bi" | "bi"
 "bi" "bi" "vebi" "bi" | "me"
END_RULES
