#!/usr/bin/env python3
# coding: utf-8
import sys
import json
# In[ ]:

# deffinition qui simule 1 hit sur quelqu'un 

def hit(dmg_min,dmg_med,dmg_max, per, vie, armor, c_crit, d_crit, c_bloc = 0, v_bloc = 0, d_ampli = 0, multi = 1):
	# without crit
	# min
	mindmg_wbloc = max( int((dmg_min + d_ampli) * per), dmg_min + d_ampli - armor) * multi - v_bloc

	mindmg_obloc = max( int((dmg_min + d_ampli) * per), dmg_min + d_ampli - armor) * multi

	#med
	dmg_wbloc = max(int((dmg_med + d_ampli) * per), ( dmg_med + d_ampli - armor)) * multi - v_bloc

	dmg_obloc = max(int((dmg_med + d_ampli) * per), ( dmg_med + d_ampli - armor)) * multi

	#max
	maxdmg_wbloc = max( int((dmg_max + d_ampli) * per), dmg_max + d_ampli - armor) * multi - v_bloc

	maxdmg_obloc = max( int((dmg_max + d_ampli) * per), dmg_max + d_ampli - armor) * multi


	# with crit
	#min
	mindmg_wcrit_wbloc = max( int(dmg_min + d_crit + d_ampli )* per, (dmg_min + d_crit + d_ampli - armor)) * multi - v_bloc

	mindmg_wcrit_obloc = max( int(dmg_min + d_crit + d_ampli )* per, (dmg_min + d_crit + d_ampli - armor)) * multi
	
	#med
	dmg_wcrit_wbloc = max( int((dmg_med + d_ampli + d_crit) * per), ( dmg_med + d_ampli + d_crit - armor)) * multi - v_bloc

	dmg_wcrit_obloc = max( int((dmg_med + d_ampli + d_crit) * per), ( dmg_med + d_ampli + d_crit - armor)) * multi

	#max
	maxdmg_wcrit_wbloc = max( int((dmg_max + d_crit + d_ampli )* per), (dmg_max + d_crit + d_ampli - armor)) * multi - v_bloc

	maxdmg_wcrit_obloc = max( int((dmg_max + d_crit + d_ampli )* per), (dmg_max + d_crit + d_ampli - armor)) * multi

	
	
	return ([mindmg_obloc,dmg_obloc,maxdmg_obloc],[mindmg_wbloc,dmg_wbloc,maxdmg_wbloc],[mindmg_wcrit_obloc,dmg_wcrit_obloc,maxdmg_wcrit_obloc],[mindmg_wcrit_wbloc,dmg_wcrit_wbloc,maxdmg_wcrit_wbloc])
	

def multihit(dmg, nbr_hit, per, vie, armor, nbr_crit = 0, c_crit=0, d_crit=0, nbr_bloc=0, c_bloc = 0, v_bloc = 0, d_ampli = 0, multi = 1):
	
	if isinstance(dmg,list):
		dmg_min = dmg[0]
		dmg_med = int((dmg[0] + dmg[1]) /2)
		dmg_max = dmg[1]
	else:
		dmg_min = int(dmg*0.8)
		dmg_med = dmg
		dmg_max = int(dmg*1.2)
	
	
	hit_crit_bloc = 0
	hit_crit = 0
	hit_bloc = 0
	hit_normal = 0

	if nbr_hit == nbr_crit and nbr_hit == nbr_bloc:
		proba = (c_crit ** nbr_hit) * (c_bloc ** nbr_hit)
	elif nbr_hit == nbr_crit:
		proba = (c_crit ** nbr_hit) * (c_bloc ** nbr_bloc) * (1-c_bloc)
	elif nbr_hit == nbr_bloc:
		proba = (c_crit ** nbr_crit) * (1-c_crit) * (c_bloc ** nbr_hit)
	else:
		proba = (c_crit ** nbr_crit) * (1-c_crit) * (c_bloc ** nbr_bloc) * (1-c_bloc)

		
	for i in range(0,nbr_hit):
		if nbr_crit != 0 and nbr_bloc != 0:
			hit_crit_bloc += 1
			nbr_crit -=1
			nbr_bloc-=1
		elif nbr_crit != 0 and nbr_bloc == 0:
			hit_crit += 1
			nbr_crit -=1
		elif nbr_crit ==0 and nbr_bloc != 0:
			hit_bloc += 1
			nbr_bloc-=1
		else:
			hit_normal += 1

	normal, bloc, crit, crit_bloc = hit(dmg_min, dmg_med, dmg_max, per, vie, armor, c_crit, d_crit, c_bloc, v_bloc, d_ampli, multi)
	tdmg = [ crit_bloc[0]*hit_crit_bloc + crit[0]*hit_crit + bloc[0]*hit_bloc + normal[0]*hit_normal, crit_bloc[1]*hit_crit_bloc + crit[1]*hit_crit + bloc[1]*hit_bloc + normal[1]*hit_normal, crit_bloc[2]*hit_crit_bloc + crit[2]*hit_crit + bloc[2]*hit_bloc + normal[2]*hit_normal ]
	
	
	return tdmg,proba


if __name__ == "__main__":
	# Get command-line args
	# Example: ./DoesItKill.py 30 2 0.3 200 20 1 0.25 12
	# Maps to: dmg, nbr_hit, per, vie, armor, nbr_crit, c_crit, d_crit, nbr_bloc, c_bloc, v_bloc, d_ampli, multi
	if len(sys.argv) < 5:
		print("Usage: ./DoesItKill.py dmg nbr_hit per vie armor [nbr_crit c_crit d_crit nbr_bloc c_bloc v_bloc d_ampli multi]")
		sys.exit(1)

	dmg_arg = sys.argv[1]
	try:
		dmg = json.loads(dmg_arg)
	except json.JSONDecodeError:
		dmg = int(dmg_arg)  
	nbr_hit = int(sys.argv[2])
	per = float(sys.argv[3])
	vie = int(sys.argv[4])
	armor = int(sys.argv[5]) 
	nbr_crit = int(sys.argv[6]) if len(sys.argv) > 6 else 0
	c_crit = float(sys.argv[7]) if len(sys.argv) > 7 else 0
	d_crit = int(sys.argv[8]) if len(sys.argv) > 8 else 0
	nbr_bloc = int(sys.argv[9]) if len(sys.argv) > 9 else 0
	c_bloc = float(sys.argv[10]) if len(sys.argv) > 10 else 0
	v_bloc = int(sys.argv[11]) if len(sys.argv) > 11 else 0
	d_ampli = int(sys.argv[12]) if len(sys.argv) > 12 else 0
	multi = float(sys.argv[13])  if len(sys.argv) > 13 else 1

	tdmg, proba = multihit(dmg, nbr_hit, per, vie, armor, nbr_crit, c_crit, d_crit, nbr_bloc, c_bloc, v_bloc, d_ampli, multi)


	result = {
		"tdmg": tdmg,
		"proba": proba
	}
	print(json.dumps(result))
