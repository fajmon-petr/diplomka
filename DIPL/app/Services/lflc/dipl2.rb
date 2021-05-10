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
 "low" "ve_low_sim" "low" | "ve_low_rank"
 "low" "ve_low_sim" "med" | "ve_low_rank"
 "low" "ve_low_sim" "high" | "low_rank"
 "low" "ve_low_sim" "ve_high" | "low_rank"
 "low" "low_sim" "low" | "ve_low_rank"
 "low" "low_sim" "med" | "ve_low_rank"
 "low" "low_sim" "high" | "low_rank"
 "low" "low_sim" "ve_high" | "low_rank"
 "low" "med_sim" "low" | "ve_low_rank"
 "low" "med_sim" "med" | "low_rank"
 "low" "med_sim" "high" | "med_rank"
 "low" "med_sim" "ve_high" | "med_rank"
 "low" "high_sim" "low" | "low_rank"
 "low" "high_sim" "med" | "med_rank"
 "low" "high_sim" "high" | "med_rank"
 "low" "high_sim" "ve_high" | "high_rank"
 "low" "ve_high_sim" "low" | "low_rank"
 "low" "ve_high_sim" "med" | "med_rank"
 "low" "ve_high_sim" "high" | "high_rank"
 "low" "ve_high_sim" "ve_high" | "very_high_rank"
 "med" "ve_low_sim" "low" | "ve_low_rank"
 "med" "ve_low_sim" "med" | "low_rank"
 "med" "ve_low_sim" "high" | "low_rank"
 "med" "ve_low_sim" "ve_high" | "med_rank"
 "med" "low_sim" "low" | "low_rank"
 "med" "low_sim" "med" | "med_rank"
 "med" "low_sim" "high" | "med_rank"
 "med" "low_sim" "ve_high" | "high_rank"
 "med" "med_sim" "low" | "low_rank"
 "med" "med_sim" "med" | "med_rank"
 "med" "med_sim" "high" | "med_rank"
 "med" "med_sim" "ve_high" | "high_rank"
 "med" "high_sim" "low" | "med_rank"
 "med" "high_sim" "med" | "med_rank"
 "med" "high_sim" "high" | "high_rank"
 "med" "high_sim" "ve_high" | "high_rank"
 "med" "ve_high_sim" "low" | "med_rank"
 "med" "ve_high_sim" "med" | "high_rank"
 "med" "ve_high_sim" "high" | "very_high_rank"
 "med" "ve_high_sim" "ve_high" | "very_high_rank"
 "high" "ve_low_sim" "low" | "low_rank"
 "high" "ve_low_sim" "med" | "med_rank"
 "high" "ve_low_sim" "high" | "med_rank"
 "high" "ve_low_sim" "ve_high" | "med_rank"
 "high" "low_sim" "low" | "med_rank"
 "high" "low_sim" "med" | "med_rank"
 "high" "low_sim" "high" | "high_rank"
 "high" "low_sim" "ve_high" | "high_rank"
 "high" "med_sim" "low" | "med_rank"
 "high" "med_sim" "med" | "high_rank"
 "high" "med_sim" "high" | "high_rank"
 "high" "med_sim" "ve_high" | "very_high_rank"
 "high" "high_sim" "low" | "med_rank"
 "high" "high_sim" "med" | "high_rank"
 "high" "high_sim" "high" | "very_high_rank"
 "high" "high_sim" "ve_high" | "very_high_rank"
 "high" "ve_high_sim" "low" | "high_rank"
 "high" "ve_high_sim" "med" | "high_rank"
 "high" "ve_high_sim" "high" | "very_high_rank"
 "high" "ve_high_sim" "ve_high" | "very_high_rank"
END_RULES
