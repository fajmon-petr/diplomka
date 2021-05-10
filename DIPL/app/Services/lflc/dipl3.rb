!COMMENT!
Enter your description of the rulebase here.
!END_COMMENT!

TypeOfDescription=linguistic
InfMethod=Fuzzy_Approximation-logical
DefuzzMethod=ModifiedCenterOfGravity
UseFuzzyFilter=false

NumberOfAntecedentVariables=3
NumberOfSuccedentVariables=1
NumberOfRules=60

AntVariable1
 name=V1
 settings=new
 context=<0,2.5,5>
 discretization=301
 UserTerm
  name=low
  type=trapezoid
  parameters= 0 0 1 1.99
 End_UserTerm
 UserTerm
  name=med
  type=trapezoid
  parameters= 1 2 3 4
 End_UserTerm
 UserTerm
  name=high
  type=trapezoid
  parameters= 3.01 4.01 5 5
 End_UserTerm
End_AntVariable1

AntVariable2
 name=V2
 settings=new
 context=<0,0.5,1>
 discretization=301
 UserTerm
  name=ve_low_sim
  type=triang
  parameters= 0 0 0.25
 End_UserTerm
 UserTerm
  name=low_sim
  type=triang
  parameters= 0 0.252 0.495
 End_UserTerm
 UserTerm
  name=med_sim
  type=triang
  parameters= 0.252 0.502 0.75
 End_UserTerm
 UserTerm
  name=high_sim
  type=triang
  parameters= 0.502 0.752 1
 End_UserTerm
 UserTerm
  name=ve_high_sim
  type=triang
  parameters= 0.752 1 1
 End_UserTerm
End_AntVariable2

AntVariable3
 name=V3
 settings=new
 context=<0,0.75,1.5>
 discretization=301
 UserTerm
  name=low
  type=trapezoid
  parameters= 0 0 0.225 0.445
 End_UserTerm
 UserTerm
  name=med
  type=trapezoid
  parameters= 0.225 0.45 0.675 0.9
 End_UserTerm
 UserTerm
  name=high
  type=trapezoid
  parameters= 0.675 0.9 1.05 1.27
 End_UserTerm
 UserTerm
  name=ve_high
  type=trapezoid
  parameters= 1.05 1.27 1.5 1.5
 End_UserTerm
End_AntVariable3

SucVariable1
 name=V4
 settings=new
 context=<0,0.5,1>
 discretization=301
 UserTerm
  name=ve_low_rank
  type=triang
  parameters= 0 0 0.25
 End_UserTerm
 UserTerm
  name=low_rank
  type=triang
  parameters= 0 0.25 0.5
 End_UserTerm
 UserTerm
  name=med_rank
  type=triang
  parameters= 0.25 0.5 0.75
 End_UserTerm
 UserTerm
  name=high_rank
  type=triang
  parameters= 0.5 0.75 1
 End_UserTerm
 UserTerm
  name=very_high_rank
  type=triang
  parameters= 0.75 1 1
 End_UserTerm
End_SucVariable1

RULES
 "low" "ve_low_sim" "low" | "ex sm"
 "low" "ve_low_sim" "med" | "ve sm"
 "low" "ve_low_sim" "high" | "ro sm"
 "low" "ve_low_sim" "ve_high" | "vr sm"
 "low" "low_sim" "low" | "si sm"
 "low" "low_sim" "med" | "ve sm"
 "low" "low_sim" "high" | "sm"
 "low" "low_sim" "ve_high" | "ml sm"
 "low" "med_sim" "low" | "ve sm"
 "low" "med_sim" "med" | "ro sm"
 "low" "med_sim" "high" | "vr sm"
 "low" "med_sim" "ve_high" | "me"
 "low" "high_sim" "low" | "me"
 "low" "high_sim" "med" | "ro bi"
 "low" "high_sim" "high" | "qr bi"
 "low" "high_sim" "ve_high" | "vr bi"
 "low" "ve_high_sim" "low" | "ml bi"
 "low" "ve_high_sim" "med" | "ro bi"
 "low" "ve_high_sim" "high" | "qr bi"
 "low" "ve_high_sim" "ve_high" | "vr bi"
 "med" "ve_low_sim" "low" | "si sm"
 "med" "ve_low_sim" "med" | "ml sm"
 "med" "ve_low_sim" "high" | "qr sm"
 "med" "ve_low_sim" "ve_high" | "me"
 "med" "low_sim" "low" | "vr sm"
 "med" "low_sim" "med" | "qr sm"
 "med" "low_sim" "high" | "me"
 "med" "low_sim" "ve_high" | "qr bi"
 "med" "med_sim" "low" | "vr sm"
 "med" "med_sim" "med" | "me"
 "med" "med_sim" "high" | "ro bi"
 "med" "med_sim" "ve_high" | "vr bi"
 "med" "high_sim" "low" | "vr bi"
 "med" "high_sim" "med" | "qr bi"
 "med" "high_sim" "high" | "ro bi"
 "med" "high_sim" "ve_high" | "ml bi"
 "med" "ve_high_sim" "low" | "me"
 "med" "ve_high_sim" "med" | "ml bi"
 "med" "ve_high_sim" "high" | "ve bi"
 "med" "ve_high_sim" "ve_high" | "si bi"
 "high" "ve_low_sim" "low" | "vr sm"
 "high" "ve_low_sim" "med" | "me"
 "high" "ve_low_sim" "high" | "me"
 "high" "ve_low_sim" "ve_high" | "vr bi"
 "high" "low_sim" "low" | "qr me"
 "high" "low_sim" "med" | "ro me"
 "high" "low_sim" "high" | "vr bi"
 "high" "low_sim" "ve_high" | "qr bi"
 "high" "med_sim" "low" | "me"
 "high" "med_sim" "med" | "qr bi"
 "high" "med_sim" "high" | "ro bi"
 "high" "med_sim" "ve_high" | "bi"
 "high" "high_sim" "low" | "me"
 "high" "high_sim" "med" | "ml bi"
 "high" "high_sim" "high" | "bi"
 "high" "high_sim" "ve_high" | "ve bi"
 "high" "ve_high_sim" "low" | "bi"
 "high" "ve_high_sim" "med" | "ve bi"
 "high" "ve_high_sim" "high" | "si bi"
 "high" "ve_high_sim" "ve_high" | "ex bi"
END_RULES
