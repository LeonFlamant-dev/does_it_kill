#!/usr/bin/env python3
# coding: utf-8
import sys
import json
# In[ ]:

# deffinition qui simule 1 hit sur quelqu'un 

def hit(dmg, per, vie, armor, c_crit, d_crit, c_bloc = 0, v_bloc = 0, d_ampli = 0, multi = 1):
	# without crit
	mindmg_wbloc = max( int((dmg * 0.8 + d_ampli) * per), int(dmg * 0.8) + d_ampli - armor) * multi - v_bloc
	proba_min_wbloc = 0.5 * (1 - c_crit) * c_bloc
	mindmg_obloc = max( int((dmg * 0.8 + d_ampli) * per), int(dmg * 0.8) + d_ampli - armor) * multi
	proba_min_obloc = 0.5 * (1 - c_crit) * (1-c_bloc)
	
	maxdmg_wbloc = max( int((dmg * 1.2 + d_ampli) * per), int(dmg * 1.2) + d_ampli - armor) * multi - v_bloc
	proba_max_wbloc = 0.5 * (1 - c_crit) * c_bloc
	maxdmg_obloc = max( int((dmg * 1.2 + d_ampli) * per), int(dmg * 1.2) + d_ampli - armor) * multi
	proba_max_obloc = 0.5 * (1 - c_crit) * (1-c_bloc)

	# with crit
	mindmg_wcrit_wbloc = max( int(dmg * 0.8 + d_crit + d_ampli )* per, (int(dmg*0.8) + d_crit + d_ampli - armor)) * multi - v_bloc
	proba_min_crit_wbloc = 0.5 * c_crit * c_bloc
	mindmg_wcrit_obloc = max( int(dmg * 0.8 + d_crit + d_ampli )* per, (int(dmg*0.8) + d_crit + d_ampli - armor)) * multi
	proba_min_crit_obloc = 0.5 * c_crit * (1-c_bloc)
	
	maxdmg_wcrit_wbloc = max( int(dmg * 1.2 + d_crit + d_ampli )* per, (int(dmg*1.2) + d_crit + d_ampli - armor)) * multi - v_bloc
	proba_max_crit_wbloc = 0.5 * c_crit * c_bloc
	maxdmg_wcrit_obloc = max( int(dmg * 1.2 + d_crit + d_ampli )* per, (int(dmg*1.2) + d_crit + d_ampli - armor)) * multi - v_bloc
	proba_max_crit_obloc = 0.5 * c_crit * (1-c_bloc)

	# dmg median

	dmg_wbloc = max(int((dmg + d_ampli) * per), int( dmg + d_ampli - armor)) * multi - v_bloc
	proba_wbloc = (1 - c_crit) * c_bloc
	dmg_obloc = max(int((dmg + d_ampli) * per), int( dmg + d_ampli - armor)) * multi
	proba_obloc = (1 - c_crit) * (1-c_bloc)

	dmg_wcrit_wbloc = max(int((dmg + d_ampli + d_crit) * per), int( dmg + d_ampli + d_crit)) * multi - v_bloc
	proba_crit_wbloc = c_crit * c_bloc
	dmg_wcrit_obloc = max(int((dmg + d_ampli + d_crit) * per), int( dmg + d_ampli + d_crit)) * multi
	proba_crit_obloc = c_crit * (1-c_bloc)
	

	#if round(proba_min_wbloc + proba_min_obloc + proba_max_wbloc + proba_max_obloc + proba_min_crit_wbloc + proba_min_crit_obloc + proba_max_crit_wbloc + proba_max_crit_obloc) == 1 :
	#   print('stat ok')
	#else:
	#   print('stat pas ok')

		
	#print("""les degats on {}% de chance d'être entre [{},{}]
	#les degats on {}% de chance d'être entre [{},{}]
	#les degats on {}% de chance d'être entre [{},{}]
	#les degats on {}% de chance d'être entre [{},{}]""".format( proba_wbloc * 100, mindmg_wbloc,maxdmg_wbloc,proba_obloc * 100,mindmg_obloc,maxdmg_obloc, proba_crit_wbloc * 100,mindmg_wcrit_wbloc,maxdmg_wcrit_wbloc,proba_crit_obloc * 100,mindmg_wcrit_obloc,maxdmg_wcrit_obloc))

	
	
	return ([mindmg_obloc,dmg_obloc,maxdmg_obloc],[mindmg_wbloc,dmg_wbloc,maxdmg_wbloc],[mindmg_wcrit_obloc,dmg_wcrit_obloc,maxdmg_wcrit_obloc],[mindmg_wcrit_wbloc,dmg_wcrit_wbloc,maxdmg_wcrit_wbloc])

def multihit(dmg, nbr_hit, per, vie, armor, nbr_crit = 0, c_crit=0, d_crit=0, nbr_bloc=0, c_bloc = 0, v_bloc = 0, d_ampli = 0, multi = 1):
	hit_crit_bloc = 0
	hit_crit = 0
	hit_bloc = 0
	hit_normal = 0
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
			
	normal, bloc, crit, crit_bloc = hit(dmg, per, vie, armor, c_crit, d_crit, c_bloc, v_bloc, d_ampli, multi)
	tdmg = [ crit_bloc[0]*hit_crit_bloc + crit[0]*hit_crit + bloc[0]*hit_bloc + normal[0]*hit_normal, crit_bloc[1]*hit_crit_bloc + crit[1]*hit_crit + bloc[1]*hit_bloc + normal[1]*hit_normal, crit_bloc[2]*hit_crit_bloc + crit[2]*hit_crit + bloc[2]*hit_bloc + normal[2]*hit_normal ]
	
	if nbr_hit == nbr_crit and nbr_hit == nbr_bloc:
		proba = (c_crit ** nbr_hit) * (c_bloc ** nbr_hit)
	elif nbr_hit == nbr_crit:
		proba = (c_crit ** nbr_hit) * (c_bloc ** nbr_bloc) * (1-c_bloc)
	elif nbr_hit == nbr_bloc:
		proba = (c_crit ** nbr_crit) * (1-c_crit) * (c_bloc ** nbr_hit)
	else:
		proba = (c_crit ** nbr_crit) * (1-c_crit) * (c_bloc ** nbr_bloc) * (1-c_bloc)

	
	return tdmg,proba


if __name__ == "__main__":
	# Get command-line args
	# Example: ./DoesItKill.py 30 2 0.3 200 20 1 0.25 12
	# Maps to: dmg, nbr_hit, per, vie, armor, nbr_crit, c_crit, d_crit, nbr_bloc, c_bloc, v_bloc, d_ampli, multi
	if len(sys.argv) < 5:
		print("Usage: ./DoesItKill.py dmg nbr_hit per vie armor [nbr_crit c_crit d_crit nbr_bloc c_bloc v_bloc d_ampli multi]")
		sys.exit(1)

	dmg = int(sys.argv[1])
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
	multi = int(sys.argv[13])  if len(sys.argv) > 13 else 1

	tdmg, proba = multihit(dmg, nbr_hit, per, vie, armor, nbr_crit, c_crit, d_crit, nbr_bloc, c_bloc, v_bloc, d_ampli, multi)


	result = {
		"tdmg": tdmg,
		"proba": proba
	}
	print(json.dumps(result))
