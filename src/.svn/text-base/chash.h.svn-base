#ifndef _GZK_CONSIST_HASH_H_
#define _GZK_CONSIST_HASH_H_
#include <list>
#include <set>
#include<stdint.h>
#include<iostream>
using namespace std;
class ConsistHash
{
	public:
		ConsistHash();
		~ConsistHash();
		bool add_node(string servername);
		bool del_node(string servername);
		string get_node(string key);
	public:
		int replicas;
		list<pair<unsigned int,string> > circle;
		set<string> serverset;

//for test
	public:
		uint32_t get_value(string key);
    void printnode();	  
};


#endif
