#include "chash.h"
#include "md5.h"
#include <iostream>
#include <algorithm>
#include <string.h>
#include <stdio.h>
/*
	brief Hashinf function to 16 bytes char array using MD%.
	@`inString` The string that you want to hash.
	@`md5pword` The resulting hash. 
*/
void
ketama_md5_digest( char* inString, unsigned char md5pword[16] )
{
    md5_state_t md5state;
    md5_init( &md5state );
    md5_append( &md5state, (unsigned char *)inString, strlen( inString ) );
    md5_finish( &md5state, md5pword );
}

/** 
  brief Hashing function, converting a string to an unsigned int by using MD5.
  @`param` inString The string that you want to hash.
  @return The resulting hash. 
*/
unsigned int
ketama_hashi( char* inString )
{
    unsigned char digest[16];
    ketama_md5_digest( inString, digest );
    return (unsigned int)(( digest[3] << 24 ) | ( digest[2] << 16 ) | ( digest[1] <<  8 ) | digest[0] );
}

int my_comp(const pair<unsigned int,string>& a,const pair<unsigned int,string>& b) 
{
  return a.first < b.first; 
}

ConsistHash:: ConsistHash()
{
	replicas = 10;
}

ConsistHash:: ~ConsistHash()
{
	
}

bool ConsistHash:: add_node(string servername)
{
	set<string>::iterator it = serverset.find(servername);
	if( serverset.end() != it )
	{
		return false;
	}
	for(int i = 0; i < replicas; i++ )
	{
		char ss[64];
		bzero(ss,sizeof(ss));
    unsigned char digest[16];
    sprintf( ss, "%s-%d", servername.c_str(), i );
    ketama_md5_digest( ss, digest );
    unsigned int point = ( digest[3] << 24 ) | ( digest[2] << 16 ) | ( digest[1] <<  8 ) | digest[0];
    circle.push_back(make_pair<unsigned int,string>(point,servername));
	}
	circle.sort(my_comp);
	serverset.insert(servername);
	return true;
}

bool ConsistHash:: del_node(string servername)
{
  set<string>::iterator iter = serverset.find(servername);
	if( serverset.end() == iter )
	{
		return false;
	}
	serverset.erase(iter);
	for(list<pair<unsigned int,string> >::iterator it=circle.begin();it!=circle.end();)
	{
		if( servername == it->second )
		{
			it = circle.erase(it);
		}
		else
		{
			it ++;
		}
	}
	return true;
}

string ConsistHash:: get_node(string key)
{
  unsigned char digest[16];
  ketama_md5_digest( const_cast<char *>(key.c_str()), digest );
  unsigned int point = ( digest[3] << 24 ) | ( digest[2] << 16 ) | ( digest[1] <<  8 ) | digest[0];
  //cout<<"[point]"<<point<<endl;
  list<pair<unsigned int,string> >::iterator it = lower_bound(circle.begin(),circle.end(),make_pair<unsigned int,string>(point,key),my_comp);
  if( it == circle.end() )
  {
  	return circle.front().second;
  }
  else
  {
  	return it->second;
  }
}


/*  for test
*/
uint32_t ConsistHash:: get_value(string key)
{
	char ss[64];
	bzero(ss,sizeof(ss));
  unsigned char digest[16];
  sprintf( ss, "%s", key.c_str() );
  ketama_md5_digest( ss, digest );
  unsigned int point = ( digest[3] << 24 ) | ( digest[2] << 16 ) | ( digest[1] <<  8 ) | digest[0];
  return point;
}

void ConsistHash:: printnode()
{
  for(list<pair<unsigned,string> >::iterator it=circle.begin();it!=circle.end();it++)
  {
  	cout<<"[point:]"<<it->first<<"[node:]"<<it->second<<endl;
  }

}
/*
int main()
{
	ConsistHash ch;
	ch.add_node("huzhan");
	ch.add_node("wangp");
	ch.printnode();
	cout<<ch.get_node("test");
	cout<<"==========================================="<<endl;
	//ch.del_node("huzhan");
	//ch.printnode();
}
*/
